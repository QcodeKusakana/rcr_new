import 'package:flutter/material.dart';
import '../widgets/navigation.dart';
import 'package:provider/provider.dart';

import '../api/api_client.dart';
import '../models/models.dart';
import '../state/session.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'card_screen.dart';
import 'wait_screen.dart';

/// Paiement de l'adhésion (1er paiement) ou renouvellement de la cotisation.
/// Le serveur recalcule TOUJOURS le montant : celui affiché ici n'est qu'indicatif.
class PayScreen extends StatefulWidget {
  const PayScreen({super.key});
  @override
  State<PayScreen> createState() => _PayScreenState();
}

class _PayScreenState extends State<PayScreen> {
  final _form = GlobalKey<FormState>();
  final _tel = TextEditingController();
  Tarifs? _tarifs;
  Periode? _periode;
  String _canal = 'mobile_money';
  bool _chargement = true, _envoi = false;
  String? _erreur;
  double _prixMensuel = 0;

  @override
  void initState() {
    super.initState();
    _tel.text = context.read<Session>().membre?.telephone ?? '';
    _charger();
  }

  @override
  void dispose() {
    _tel.dispose();
    super.dispose();
  }

  Future<void> _charger() async {
    final s = context.read<Session>();
    final m = s.membre;
    if (m == null) return;
    try {
      final t = await s.tarifs(forcer: true);
      if (!mounted) return;
      setState(() {
        _tarifs = t;
        if (m.tarifMontant != null && (m.tarifMois ?? 0) > 0) {
          _prixMensuel = m.tarifMontant! / m.tarifMois!;
        }
        _periode = t.periodes.isEmpty ? null : t.periodes.firstWhere((p) => p.id == m.idCot, orElse: () => t.periodes.first);
        _chargement = false;
      });
    } on ApiException catch (e) {
      if (mounted) setState(() { _erreur = e.message; _chargement = false; });
    }
  }

  double get _montant => _periode == null ? 0 : _prixMensuel * _periode!.mois;

  Future<void> _payer() async {
    if (!_form.currentState!.validate() || _periode == null) return;
    setState(() { _envoi = true; _erreur = null; });
    final s = context.read<Session>();
    try {
      final r = await s.api.post('/paiements', {
        'canal': _canal,
        'telephone': _canal == 'mobile_money' ? _tel.text.trim() : '',
        'id_periode': _periode!.id,
      });
      final id = (r['paiement']['id'] as num).toInt();
      final url = r['redirect_url']?.toString();
      if (!mounted) return;
      if (url != null && url.isNotEmpty) {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => CardScreen(url: url)));
      }
      if (!mounted) return;
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => WaitScreen(suivi: Suivi(don: false, id: id), titre: 'Paiement de la cotisation')));
    } on ApiException catch (e) {
      if (mounted) setState(() => _erreur = e.message);
    } finally {
      if (mounted) setState(() => _envoi = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final m = context.watch<Session>().membre;
    if (m == null) return const Scaffold(body: SizedBox.shrink()); // session fermée pendant l'écran (jeton expiré)
    final premier = !m.premierPaiementFait;
    return Scaffold(
      appBar: rcrAppBar(premier ? "Payer mon adhésion" : 'Renouveler ma cotisation'),
      body: _chargement
          ? const Center(child: CircularProgressIndicator())
          : ListView(padding: const EdgeInsets.all(16), children: [
              if (_erreur != null) ErrorBox(_erreur!),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(m.nomComplet, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
                    const SizedBox(height: 2),
                    Text('${m.categorie} · ${m.grade} · ${m.code}', style: const TextStyle(color: Rcr.soft)),
                  ]),
                ),
              ),
              if (_tarifs != null && _prixMensuel > 0) ...[
                const SectionTitle('Période de cotisation'),
                ..._tarifs!.periodes.map((p) => RadioListTile<int>(
                      value: p.id, groupValue: _periode?.id, onChanged: (v) => setState(() => _periode = p),
                      title: Text(p.nom), subtitle: Text('${p.mois} mois'),
                      secondary: Text(money(_prixMensuel * p.mois), style: const TextStyle(fontWeight: FontWeight.w700)),
                      contentPadding: EdgeInsets.zero,
                    )),
              ] else
                const ErrorBox("Ce tarif n'est plus disponible. Contactez le secrétariat du RCR."),
              const SectionTitle('Moyen de paiement'),
              SegmentedButton<String>(
                segments: const [
                  ButtonSegment(value: 'mobile_money', label: Text('Mobile Money'), icon: Icon(Icons.phone_android)),
                  ButtonSegment(value: 'carte', label: Text('Carte'), icon: Icon(Icons.credit_card)),
                ],
                selected: {_canal},
                onSelectionChanged: (v) => setState(() => _canal = v.first),
              ),
              const SizedBox(height: 14),
              Form(
                key: _form,
                child: _canal == 'mobile_money'
                    ? Column(children: [
                        TextFormField(
                          controller: _tel, keyboardType: TextInputType.phone,
                          decoration: const InputDecoration(labelText: 'Numéro Mobile Money', hintText: '0812345678', prefixIcon: Icon(Icons.phone_outlined)),
                          validator: (v) => RegExp(r'^\+?[0-9 ]{9,16}$').hasMatch((v ?? '').trim()) ? null : 'Numéro invalide',
                        ),
                        const SizedBox(height: 8),
                        const Text('Vous recevrez une demande de confirmation sur ce téléphone : saisissez votre code PIN pour valider.', style: TextStyle(color: Rcr.soft, fontSize: 13)),
                      ])
                    : const Text('Vous serez redirigé vers la page sécurisée de paiement par carte (Visa / MasterCard).', style: TextStyle(color: Rcr.soft, fontSize: 13)),
              ),
              const SizedBox(height: 22),
              BusyButton(busy: _envoi, label: 'Payer ${money(_montant)}', icon: Icons.lock_outline, onPressed: _prixMensuel > 0 ? _payer : null),
              const SizedBox(height: 10),
              const Text('Paiement sécurisé par FlexPay. Votre cotisation est validée automatiquement dès réception du paiement.',
                  textAlign: TextAlign.center, style: TextStyle(color: Rcr.soft, fontSize: 12.5)),
            ]),
    );
  }
}
