import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../state/session.dart';
import '../../theme.dart';
import '../../widgets/common.dart';
import '../don_screen.dart';
import '../pay_screen.dart';

class AccueilTab extends StatelessWidget {
  final void Function(int) onAller;
  const AccueilTab({super.key, required this.onAller});

  @override
  Widget build(BuildContext context) {
    final s = context.watch<Session>();
    final m = s.membre;
    if (m == null) return const SizedBox.shrink();
    final premier = !m.premierPaiementFait;
    final bientot = m.aJour && (m.joursRestants ?? 999) <= 30;

    return RefreshIndicator(
      onRefresh: () => s.refresh(),
      child: ListView(padding: const EdgeInsets.all(16), physics: const AlwaysScrollableScrollPhysics(), children: [
        // Carte de membre
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            gradient: const LinearGradient(colors: [Rcr.ink, Rcr.ink2], begin: Alignment.topLeft, end: Alignment.bottomRight),
            borderRadius: BorderRadius.circular(18),
          ),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              CircleAvatar(
                radius: 32, backgroundColor: Rcr.paper2,
                backgroundImage: m.photoUrl == null ? null : NetworkImage(m.photoUrl!),
                onBackgroundImageError: m.photoUrl == null ? null : (_, __) {},
                child: m.photoUrl == null ? const Icon(Icons.person, color: Rcr.soft, size: 32) : null,
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(m.nomComplet, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800)),
                  const SizedBox(height: 3),
                  Text('${m.categorie} · ${m.grade}', style: const TextStyle(color: Color(0xFFD9CFB5))),
                ]),
              ),
            ]),
            const SizedBox(height: 16),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Text(m.code, style: const TextStyle(color: Rcr.gold2, fontWeight: FontWeight.w700, letterSpacing: 1.2, fontSize: 15)),
              Container(color: Colors.white, padding: const EdgeInsets.all(2), child: StatusChip(m.statut)),
            ]),
          ]),
        ),
        const SizedBox(height: 14),
        // Cotisation
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Text('Ma cotisation', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
              const SizedBox(height: 10),
              if (premier) ...[
                const Text("Votre adhésion n'est pas encore validée : elle l'est automatiquement dès que votre paiement est reçu."),
              ] else ...[
                _ligne('Échéance', dateFr(m.dateEcheance)),
                if (m.joursRestants != null)
                  _ligne('Jours restants', m.joursRestants! >= 0 ? '${m.joursRestants} jour(s)' : 'Échue depuis ${-m.joursRestants!} jour(s)'),
                if (m.periode.isNotEmpty) _ligne('Période', m.periode),
              ],
              if (m.tarifMontant != null) _ligne(premier ? 'Montant' : 'Renouvellement', money(m.tarifMontant!)),
              const SizedBox(height: 14),
              FilledButton.icon(
                onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PayScreen())),
                icon: Icon(premier ? Icons.lock_outline : Icons.autorenew),
                label: Text(premier ? "Payer mon adhésion" : (m.aJour && !bientot ? 'Payer à l\'avance' : 'Renouveler ma cotisation')),
              ),
            ]),
          ),
        ),
        const SizedBox(height: 14),
        Row(children: [
          Expanded(
            child: OutlinedButton.icon(
              onPressed: m.aJour
                  ? () => ouvrirPdf(context, () => s.api.download('/me/carte'), 'carte_membre_${m.code}')
                  : () => toast(context, 'Votre carte est disponible dès que votre cotisation est à jour.'),
              icon: const Icon(Icons.badge_outlined), label: const Text('Ma carte'),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: OutlinedButton.icon(
              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const DonScreen())),
              icon: const Icon(Icons.favorite_border), label: const Text('Faire un don'),
            ),
          ),
        ]),
        const SizedBox(height: 10),
        TextButton(onPressed: () => onAller(1), child: const Text('Voir mon historique de paiements')),
      ]),
    );
  }

  Widget _ligne(String a, String b) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 3),
        child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          Text(a, style: const TextStyle(color: Rcr.soft)),
          Flexible(child: Text(b, textAlign: TextAlign.right, style: const TextStyle(fontWeight: FontWeight.w700))),
        ]),
      );
}
