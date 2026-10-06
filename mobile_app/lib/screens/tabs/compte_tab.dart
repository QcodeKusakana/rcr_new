import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../state/session.dart';
import '../../theme.dart';
import '../../widgets/common.dart';
import '../password_screen.dart';

class CompteTab extends StatelessWidget {
  const CompteTab({super.key});

  @override
  Widget build(BuildContext context) {
    final s = context.watch<Session>();
    final m = s.membre;
    if (m == null) return const SizedBox.shrink();
    Widget ligne(String a, String b) => ListTile(dense: true, title: Text(a, style: const TextStyle(color: Rcr.soft, fontSize: 13)), subtitle: Text(b.isEmpty ? '—' : b, style: const TextStyle(fontSize: 15, color: Colors.black87)));
    return ListView(padding: const EdgeInsets.all(16), children: [
      Card(child: Column(children: [
        ligne('Nom complet', m.nomComplet), ligne("Code d'adhésion", m.code), ligne('E-mail', m.email),
        ligne('Téléphone', m.telephone), ligne('Province', m.province), ligne('Catégorie / grade', '${m.categorie} · ${m.grade}'),
      ])),
      const SizedBox(height: 14),
      OutlinedButton.icon(
        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PasswordScreen())),
        icon: const Icon(Icons.lock_reset), label: const Text('Changer mon mot de passe'),
      ),
      const SizedBox(height: 10),
      OutlinedButton.icon(
        onPressed: () => launchUrl(Uri.parse('https://rcr.cd'), mode: LaunchMode.externalApplication),
        icon: const Icon(Icons.public), label: const Text('Site du RCR'),
      ),
      const SizedBox(height: 10),
      FilledButton.icon(
        style: FilledButton.styleFrom(backgroundColor: Rcr.red),
        onPressed: () async {
          final ok = await showDialog<bool>(
            context: context,
            builder: (c) => AlertDialog(
              title: const Text('Se déconnecter ?'),
              actions: [TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Annuler')), TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Déconnexion'))],
            ),
          );
          if (ok == true && context.mounted) {
            await context.read<Session>().logout();
            if (context.mounted) toast(context, 'Vous êtes déconnecté.');
          }
        },
        icon: const Icon(Icons.logout), label: const Text('Se déconnecter'),
      ),
    ]);
  }
}
