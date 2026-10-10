import 'package:flutter/material.dart';
import '../widgets/navigation.dart';
import 'package:url_launcher/url_launcher.dart';

import '../theme.dart';

/// Paiement par carte FlexPay : la page sécurisée s'ouvre dans le navigateur du téléphone.
/// Au retour dans l'application, le membre appuie sur « J'ai terminé » : le statut réel est ensuite
/// vérifié auprès du serveur RCR (la page affichée par le navigateur n'est jamais une preuve de paiement).
class CardScreen extends StatefulWidget {
  final String url;
  const CardScreen({super.key, required this.url});
  @override
  State<CardScreen> createState() => _CardScreenState();
}

class _CardScreenState extends State<CardScreen> {
  bool _ouvert = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _ouvrir());
  }

  Future<void> _ouvrir() async {
    final ok = await launchUrl(Uri.parse(widget.url), mode: LaunchMode.externalApplication);
    if (mounted) setState(() => _ouvert = ok);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: rcrAppBar('Paiement par carte'),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(28),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Icon(Icons.credit_card, size: 56, color: Rcr.ink),
            const SizedBox(height: 16),
            Text(
              _ouvert
                  ? 'La page de paiement sécurisée FlexPay est ouverte dans votre navigateur. Une fois le paiement effectué, revenez ici.'
                  : 'Ouverture de la page de paiement sécurisée…',
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            FilledButton(onPressed: () => Navigator.of(context).pop(true), child: const Text("J'ai terminé le paiement")),
            const SizedBox(height: 10),
            OutlinedButton(onPressed: _ouvrir, child: const Text('Rouvrir la page de paiement')),
            const SizedBox(height: 10),
            TextButton(onPressed: () => Navigator.of(context).pop(false), child: const Text('Annuler')),
          ]),
        ),
      ),
    );
  }
}
