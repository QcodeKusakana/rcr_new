import 'dart:async';

import 'package:flutter/material.dart';
import '../widgets/navigation.dart';
import 'package:provider/provider.dart';

import '../api/api_client.dart';
import '../models/models.dart';
import '../state/session.dart';
import '../theme.dart';
import '../widgets/common.dart';

enum _Etat { attente, paye, echec, enAttenteLongue }

/// Suivi d'un paiement (cotisation ou don) : interroge l'API toutes les 4 s.
/// L'API vérifie auprès de FlexPay côté serveur : jamais de « payé » sur la seule foi de l'application.
/// Après 90 s sans réponse, le paiement reste ouvert côté serveur (une confirmation tardive est possible).
class WaitScreen extends StatefulWidget {
  final Suivi suivi;
  final String titre;
  const WaitScreen({super.key, required this.suivi, required this.titre});
  @override
  State<WaitScreen> createState() => _WaitScreenState();
}

class _WaitScreenState extends State<WaitScreen> {
  Timer? _timer;
  _Etat _etat = _Etat.attente;
  String _statut = 'processing';
  int _ecoule = 0;
  bool _expire = false, _enCours = false;
  String? _info;

  String get _base => widget.suivi.don ? '/dons/${widget.suivi.id}' : '/paiements/${widget.suivi.id}';
  Map<String, String>? get _query => widget.suivi.cle == null ? null : {'k': widget.suivi.cle!};
  bool get _connecte => context.read<Session>().membre != null;

  @override
  void initState() {
    super.initState();
    _timer = Timer.periodic(const Duration(seconds: 4), (_) => _tick());
    WidgetsBinding.instance.addPostFrameCallback((_) => _tick());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _tick() async {
    if (_enCours || !mounted) return;
    _ecoule += 4;
    await _verifier();
    if (mounted && _etat == _Etat.attente && _ecoule >= 90 && !_expire) {
      _expire = true;
      try {
        final r = await context.read<Session>().api.post('$_base/expirer${_query == null ? '' : '?k=${widget.suivi.cle}'}');
        _appliquer(r['statut'].toString());
      } on ApiException catch (_) {}
      if (mounted && _etat == _Etat.attente) setState(() => _etat = _Etat.enAttenteLongue);
    }
  }

  Future<void> _verifier() async {
    _enCours = true;
    try {
      final s = context.read<Session>();
      final r = await s.api.get('$_base/statut', query: _query);
      _appliquer(r['statut'].toString());
    } on ApiException catch (e) {
      if (mounted && !e.isNetwork) setState(() => _info = e.message);
    } finally {
      _enCours = false;
    }
  }

  void _appliquer(String statut) {
    if (!mounted) return;
    _statut = statut;
    if (statut == 'paid') {
      _timer?.cancel();
      if (_connecte) context.read<Session>().refresh();
      setState(() => _etat = _Etat.paye);
    } else if (statut == 'failed' || statut == 'cancelled') {
      _timer?.cancel();
      setState(() => _etat = _Etat.echec);
    }
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: _etat != _Etat.attente,
      child: Scaffold(
        appBar: rcrAppBar(widget.titre, automaticallyImplyLeading: _etat != _Etat.attente),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(28),
            child: switch (_etat) {
              _Etat.attente => Column(mainAxisSize: MainAxisSize.min, children: [
                  const SizedBox(width: 64, height: 64, child: CircularProgressIndicator(strokeWidth: 5, color: Rcr.gold)),
                  const SizedBox(height: 26),
                  const Text('En attente de confirmation', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 10),
                  const Text('Validez la demande de paiement sur votre téléphone (saisissez votre code PIN). Ne fermez pas cet écran.', textAlign: TextAlign.center),
                  if (_info != null) ...[const SizedBox(height: 14), Text(_info!, style: const TextStyle(color: Rcr.red), textAlign: TextAlign.center)],
                ]),
              _Etat.paye => _resultat(Icons.check_circle, Rcr.green, 'Paiement confirmé', widget.suivi.don ? 'Merci pour votre soutien au RCR.' : 'Votre cotisation est enregistrée et validée automatiquement.'),
              _Etat.echec => _resultat(Icons.cancel, Rcr.red, _statut == 'cancelled' ? 'Paiement annulé' : 'Paiement non abouti', "Aucun montant n'a été débité. Vous pouvez réessayer."),
              _Etat.enAttenteLongue => _resultat(Icons.hourglass_top, Rcr.gold, 'Confirmation en attente',
                  "Nous n'avons pas encore reçu la confirmation de l'opérateur. Si vous avez validé le paiement, il sera pris en compte automatiquement dès que FlexPay le confirmera (vérifiez l'historique dans quelques minutes)."),
            },
          ),
        ),
      ),
    );
  }

  Widget _resultat(IconData icon, Color color, String titre, String texte) {
    final peutRecu = _etat == _Etat.paye && _connecte;
    return Column(mainAxisSize: MainAxisSize.min, children: [
      Icon(icon, size: 84, color: color),
      const SizedBox(height: 18),
      Text(titre, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800), textAlign: TextAlign.center),
      const SizedBox(height: 10),
      Text(texte, textAlign: TextAlign.center, style: const TextStyle(color: Rcr.soft)),
      const SizedBox(height: 28),
      if (_etat == _Etat.enAttenteLongue) ...[
        FilledButton(onPressed: () async { await _verifier(); if (mounted && _etat == _Etat.enAttenteLongue) toast(context, 'Pas encore confirmé.'); }, child: const Text('Vérifier maintenant')),
        const SizedBox(height: 10),
      ],
      if (peutRecu) ...[
        OutlinedButton.icon(
          onPressed: () => ouvrirPdf(context, () => context.read<Session>().api.download('$_base/recu'), 'recu_rcr_${widget.suivi.id}'),
          icon: const Icon(Icons.receipt_long_outlined), label: const Text('Voir mon reçu'),
        ),
        const SizedBox(height: 10),
      ],
      FilledButton(onPressed: () => Navigator.of(context).popUntil((r) => r.isFirst), child: Text(_etat == _Etat.echec ? 'Retour' : 'Terminer')),
    ]);
  }
}
