import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../api/api_client.dart';
import '../../models/models.dart';
import '../../state/session.dart';
import '../../theme.dart';
import '../../widgets/common.dart';
import '../pay_screen.dart';

class PaiementsTab extends StatefulWidget {
  const PaiementsTab({super.key});
  @override
  State<PaiementsTab> createState() => _PaiementsTabState();
}

class _PaiementsTabState extends State<PaiementsTab> {
  List<Paiement>? _items;
  String? _erreur;

  @override
  void initState() {
    super.initState();
    _charger();
  }

  Future<void> _charger() async {
    final s = context.read<Session>();
    try {
      final r = await s.api.get('/paiements');
      if (mounted) setState(() { _items = (r['items'] as List).map((e) => Paiement.fromJson(e as Map<String, dynamic>)).toList(); _erreur = null; });
    } on ApiException catch (e) {
      if (mounted) setState(() => _erreur = e.message);
    }
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
      child: liste.isEmpty
          ? ListView(children: [
              const SizedBox(height: 80),
              const Icon(Icons.receipt_long_outlined, size: 56, color: Rcr.soft),
              const SizedBox(height: 12),
              const Center(child: Text('Aucun paiement pour le moment')),
              Padding(padding: const EdgeInsets.all(24), child: FilledButton(
                onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PayScreen())),
                child: const Text('Payer ma cotisation'),
              )),
            ])
          : ListView.separated(
              padding: const EdgeInsets.all(16), physics: const AlwaysScrollableScrollPhysics(),
              itemCount: liste.length, separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) {
                final p = liste[i];
                return Card(
                  child: ListTile(
                    title: Text(p.type == 'adhesion' ? 'Adhésion' : 'Cotisation', style: const TextStyle(fontWeight: FontWeight.w700)),
                    subtitle: Text('${dateFr(p.date)} · ${p.canal == 'carte' ? 'Carte' : 'Mobile Money'}\n${p.reference}', style: const TextStyle(fontSize: 12)),
                    isThreeLine: true,
                    trailing: Column(mainAxisAlignment: MainAxisAlignment.center, crossAxisAlignment: CrossAxisAlignment.end, children: [
                      Text(money(p.montant, p.devise), style: const TextStyle(fontWeight: FontWeight.w800)),
                      const SizedBox(height: 4),
                      StatusChip(p.statut),
                    ]),
                    onTap: p.paye
                        ? () => ouvrirPdf(context, () => context.read<Session>().api.download('/paiements/${p.id}/recu'), 'recu_${p.reference}')
                        : null,
                  ),
                );
              },
            ),
    );
  }
}
