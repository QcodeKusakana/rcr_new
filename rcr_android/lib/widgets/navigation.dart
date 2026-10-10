import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../theme.dart';
import 'common.dart';

/// Barre supérieure commune : logo RCR + « RCR » + titre de l'écran.
/// La flèche de retour est ajoutée automatiquement par Flutter quand une page précède l'écran.
PreferredSizeWidget rcrAppBar(String titre, {List<Widget>? actions, bool automaticallyImplyLeading = true}) {
  return AppBar(
    automaticallyImplyLeading: automaticallyImplyLeading,
    actions: actions,
    titleSpacing: 0,
    bottom: const PreferredSize(
      preferredSize: Size.fromHeight(3),
      child: ColoredBox(color: Rcr.gold2, child: SizedBox(height: 3, width: double.infinity)),
    ),
    title: Row(children: [
      Container(
        padding: const EdgeInsets.all(2),
        decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
        child: Image.asset('assets/logo.png', height: 28, width: 28),
      ),
      const SizedBox(width: 8),
      const Text('RCR', style: TextStyle(fontWeight: FontWeight.w800, letterSpacing: 1.5, color: Rcr.gold2)),
      Container(width: 1, height: 18, margin: const EdgeInsets.symmetric(horizontal: 10), color: Colors.white38),
      Expanded(child: Text(titre, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w600))),
    ]),
  );
}

/// Demande confirmation puis ferme l'application. La session et le jeton enregistrés sont conservés.
Future<void> confirmerQuitter(BuildContext context) async {
  final ok = await showDialog<bool>(
    context: context,
    builder: (c) => AlertDialog(
      icon: const Icon(Icons.exit_to_app, color: Rcr.ink),
      title: const Text("Quitter l'application ?"),
      content: const Text('Vous resterez connecté : vos données sont conservées.'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Annuler')),
        TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Quitter')),
      ],
    ),
  );
  if (ok == true) await SystemNavigator.pop();
}

/// Écran racine : 1er retour → message, 2e retour dans les 2 s → fermeture.
/// [onRetourInterne] permet de consommer le retour (ex. revenir à l'onglet Accueil) avant de quitter.
class DoubleRetourQuitter extends StatefulWidget {
  final Widget child;
  final bool Function()? onRetourInterne;
  const DoubleRetourQuitter({super.key, required this.child, this.onRetourInterne});
  @override
  State<DoubleRetourQuitter> createState() => _DoubleRetourQuitterState();
}

class _DoubleRetourQuitterState extends State<DoubleRetourQuitter> {
  DateTime? _dernier;

  void _retour(bool didPop, Object? result) {
    if (didPop) return;
    if (widget.onRetourInterne?.call() ?? false) return;
    final now = DateTime.now();
    if (_dernier != null && now.difference(_dernier!) < const Duration(seconds: 2)) {
      SystemNavigator.pop();
      return;
    }
    _dernier = now;
    toast(context, "Appuyez encore une fois pour quitter l'application");
  }

  @override
  Widget build(BuildContext context) => PopScope(canPop: false, onPopInvokedWithResult: _retour, child: widget.child);
}
