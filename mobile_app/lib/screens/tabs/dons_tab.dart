import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../api/api_client.dart';
import '../../models/models.dart';
import '../../state/session.dart';
import '../../theme.dart';
import '../../widgets/common.dart';
import '../don_screen.dart';

class DonsTab extends StatefulWidget {
  const DonsTab({super.key});
  @override
  State<DonsTab> createState() => _DonsTabState();
}

class _DonsTabState extends State<DonsTab> {
  List<Don>? _items;
  String? _erreur;

  @override
  void initState() {
    super.initState();
    _charger();
  }

  Future<void> _charger() async {
    final s = context.read<Session>();
    try {
      final r = await s.api.get('/dons');
      if (mounted) setState(() { _items = (r['items'] as List).map((e) => Don.fromJson(e as Map<String, dynamic>)).toList(); _erreur = null; });
    } on ApiException catch (e) {
      if (mounted) setState(() => _erreur = e.message);
    }
  }

  Future<void> _ouvrir(Widget w) async {
    await Navigator.push(context, MaterialPageRoute(builder: (_) => w));
    if (mounted) _charger();
  }

  @override
  Widget build(BuildContext context) {
    if (_items == null && _erreur == null) return const Center(child: CircularProgressIndicator());
    if (_erreur != null && _items == null) {
      return Center(child: Padding(padding: const EdgeInsets.all(24), child: Column(mainAxisSize: MainAxisSize.min, children: [
        ErrorBox(_erreur!), FilledButton(onPressed: _charger, child: const Text('Réessayer')),
      ])));
    }
    final liste = _items!;
    return RefreshIndicator(
      onRefresh: _charger,
      child: ListView(padding: const EdgeInsets.all(16), physics: const AlwaysScrollableScrollPhysics(), children: [
        FilledButton.icon(onPressed: () => _ouvrir(const DonScreen()), icon: const Icon(Icons.favorite), label: const Text('Faire un don')),
        const SizedBox(height: 14),
        if (liste.isEmpty)
          const Padding(padding: EdgeInsets.all(30), child: Center(child: Text('Aucun don pour le moment', style: TextStyle(color: Rcr.soft)))),
        ...liste.map((d) => Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Row(children: [
                      Expanded(child: Text(d.type == 'regulier' ? 'Don régulier${d.frequence != null ? ' · ${d.frequence}' : ''}' : 'Don ponctuel',
                          style: const TextStyle(fontWeight: FontWeight.w700))),
                      Text(money(d.montant), style: const TextStyle(fontWeight: FontWeight.w800)),
                    ]),
                    const SizedBox(height: 4),
                    Text('${dateFr(d.date)} · ${d.canal == 'carte' ? 'Carte' : 'Mobile Money'}', style: const TextStyle(color: Rcr.soft, fontSize: 13)),
                    const SizedBox(height: 8),
                    Row(children: [
                      StatusChip(d.statut),
                      if (d.aRenouveler) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(color: Rcr.gold.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(99)),
                          child: const Text('À renouveler', style: TextStyle(color: Rcr.gold, fontSize: 12, fontWeight: FontWeight.w700)),
                        ),
                      ],
                      const Spacer(),
                      if (d.paye) TextButton(
                        onPressed: () => ouvrirPdf(context, () => context.read<Session>().api.download('/dons/${d.id}/recu'), 'recu_${d.reference}'),
                        child: const Text('Reçu'),
                      ),
                    ]),
                    if (d.type == 'regulier' && d.paye && d.prochaineEcheance != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 6),
                        child: Text('Prochaine échéance : ${dateFr(d.prochaineEcheance)}', style: const TextStyle(fontSize: 13, color: Rcr.soft)),
                      ),
                    if (d.aRenouveler)
                      Padding(
                        padding: const EdgeInsets.only(top: 10),
                        child: FilledButton.icon(
                          onPressed: () => _ouvrir(DonScreen(renouveler: d)),
                          icon: const Icon(Icons.autorenew), label: const Text('Renouveler ce don'),
                          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(44)),
                        ),
                      ),
                  ]),
                ),
              ),
            )),
      ]),
    );
  }
}
