import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../api/api_client.dart';
import '../models/models.dart';
import '../state/session.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'card_screen.dart';
import 'wait_screen.dart';

/// Don ponctuel ou régulier (avec ou sans compte), ou renouvellement d'un don régulier existant ([renouveler]).
class DonScreen extends StatefulWidget {
  final Don? renouveler;
  const DonScreen({super.key, this.renouveler});
  @override
  State<DonScreen> createState() => _DonScreenState();
}

class _DonScreenState extends State<DonScreen> {
  final _form = GlobalKey<FormState>();
  final _montant = TextEditingController(), _tel = TextEditingController();
  final _nom = TextEditingController(), _prenom = TextEditingController(), _email = TextEditingController();
  String _type = 'ponctuel', _freq = 'mensuel', _canal = 'mobile_money';
  bool _envoi = false;
  String? _erreur;

  static const _montants = [5, 10, 25, 50, 100];
  static const _frequences = {'mensuel': 'Mensuel', 'trimestriel': 'Trimestriel', 'semestriel': 'Semestriel', 'annuel': 'Annuel'};

  bool get _renouvellement => widget.renouveler != null;

  @override
  void initState() {
    super.initState();
    final m = context.read<Session>().membre;
    if (m != null) _tel.text = m.telephone;
    if (_renouvellement) {
      final d = widget.renouveler!;
      _type = 'regulier';
      _freq = d.frequence ?? 'mensuel';
      _montant.text = d.montant == d.montant.roundToDouble() ? d.montant.toStringAsFixed(0) : d.montant.toString();
    }
  }

  @override
  void dispose() {
    for (final c in [_montant, _tel, _nom, _prenom, _email]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _donner() async {
    if (!_form.currentState!.validate()) return;
    setState(() { _envoi = true; _erreur = null; });
    final s = context.read<Session>();
    try {
      final corps = <String, dynamic>{
        'montant': _montant.text.trim().replaceAll(',', '.'),
        'canal': _canal,
        'telephone': _tel.text.trim(),
      };
      late final Map<String, dynamic> r;
      if (_renouvellement) {
        r = await s.api.post('/dons/${widget.renouveler!.id}/renouveler', corps);
      } else {
        corps['type_don'] = _type;
        if (_type == 'regulier') corps['frequence'] = _freq;
        if (s.membre == null) {
          corps['nom'] = _nom.text.trim();
          corps['prenom'] = _prenom.text.trim();
          corps['email'] = _email.text.trim();
        }
        r = await s.api.post('/dons', corps);
      }
      final don = r['don'] as Map<String, dynamic>;
      final url = r['redirect_url']?.toString();
      if (!mounted) return;
      if (url != null && url.isNotEmpty) {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => CardScreen(url: url)));
      }
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => WaitScreen(
              suivi: Suivi(don: true, id: (don['id'] as num).toInt(), cle: s.membre == null ? don['suivi'].toString() : null),
              titre: 'Votre don',
            )),
      );
    } on ApiException catch (e) {
      if (mounted) setState(() => _erreur = e.message);
    } finally {
      if (mounted) setState(() => _envoi = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final connecte = context.watch<Session>().membre != null;
    return Scaffold(
      appBar: AppBar(title: Text(_renouvellement ? 'Renouveler mon don' : 'Soutenir le RCR')),
      body: Form(
        key: _form,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          if (_erreur != null) ErrorBox(_erreur!),
          if (!_renouvellement) ...[
            const Text('Défendez vos idées, faites un don !', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: Rcr.ink)),
            const SizedBox(height: 4),
            const Text('Choisissez librement le montant et la fréquence de votre soutien.', style: TextStyle(color: Rcr.soft)),
            const SectionTitle('Type de don'),
            SegmentedButton<String>(
              segments: const [ButtonSegment(value: 'ponctuel', label: Text('Ponctuel')), ButtonSegment(value: 'regulier', label: Text('Régulier'))],
              selected: {_type},
              onSelectionChanged: (v) => setState(() => _type = v.first),
            ),
            if (_type == 'regulier') ...[
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _freq, decoration: const InputDecoration(labelText: 'Fréquence'),
                items: _frequences.entries.map((e) => DropdownMenuItem(value: e.key, child: Text(e.value))).toList(),
                onChanged: (v) => setState(() => _freq = v ?? 'mensuel'),
              ),
              const Padding(
                padding: EdgeInsets.only(top: 6),
                child: Text('Le premier don est réglé maintenant ; les suivants vous sont rappelés à chaque échéance.', style: TextStyle(color: Rcr.soft, fontSize: 12.5)),
              ),
            ],
          ] else
            Card(child: Padding(padding: const EdgeInsets.all(14), child: Text('Don régulier ${_frequences[_freq]?.toLowerCase() ?? ''} — vous pouvez ajuster le montant.'))),
          const SectionTitle('Montant (USD)'),
          Wrap(spacing: 8, runSpacing: 8, children: _montants.map((v) => ChoiceChip(
                label: Text('$v \$'), selected: _montant.text == '$v',
                onSelected: (_) => setState(() => _montant.text = '$v'),
              )).toList()),
          const SizedBox(height: 12),
          TextFormField(
            controller: _montant, keyboardType: const TextInputType.numberWithOptions(decimal: true),
            decoration: const InputDecoration(labelText: 'Autre montant', suffixText: 'USD'),
            onChanged: (_) => setState(() {}),
            validator: (v) {
              final n = double.tryParse((v ?? '').trim().replaceAll(',', '.'));
              if (n == null || n <= 0 || n > 100000) return 'Montant invalide (100 000 USD maximum)';
              return null;
            },
          ),
          if (!connecte && !_renouvellement) ...[
            const SectionTitle('Vos informations'),
            TextFormField(controller: _nom, textCapitalization: TextCapitalization.words, decoration: const InputDecoration(labelText: 'Nom'), validator: (v) => (v ?? '').trim().isEmpty ? 'Champ obligatoire' : null),
            const SizedBox(height: 12),
            TextFormField(controller: _prenom, textCapitalization: TextCapitalization.words, decoration: const InputDecoration(labelText: 'Prénom'), validator: (v) => (v ?? '').trim().isEmpty ? 'Champ obligatoire' : null),
            const SizedBox(height: 12),
            TextFormField(
              controller: _email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Adresse e-mail'),
              validator: (v) => RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch((v ?? '').trim()) ? null : 'Adresse e-mail invalide',
            ),
          ],
          const SectionTitle('Moyen de paiement'),
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: 'mobile_money', label: Text('Mobile Money'), icon: Icon(Icons.phone_android)),
              ButtonSegment(value: 'carte', label: Text('Carte'), icon: Icon(Icons.credit_card)),
            ],
            selected: {_canal},
            onSelectionChanged: (v) => setState(() => _canal = v.first),
          ),
          const SizedBox(height: 12),
          if (_canal == 'mobile_money')
            TextFormField(
              controller: _tel, keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: 'Numéro Mobile Money', hintText: '0812345678', prefixIcon: Icon(Icons.phone_outlined)),
              validator: (v) => RegExp(r'^\+?[0-9 ]{9,16}$').hasMatch((v ?? '').trim()) ? null : 'Numéro invalide',
            ),
          const SizedBox(height: 22),
          BusyButton(busy: _envoi, label: 'Faire mon don', icon: Icons.favorite, onPressed: _donner),
          const SizedBox(height: 10),
          const Text('Paiement sécurisé par FlexPay.', textAlign: TextAlign.center, style: TextStyle(color: Rcr.soft, fontSize: 12.5)),
        ]),
      ),
    );
  }
}
