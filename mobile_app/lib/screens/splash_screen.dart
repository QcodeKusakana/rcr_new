import 'package:flutter/material.dart';

import '../theme.dart';

/// Écran de chargement : logo RCR, nom du mouvement et message d'accueil.
/// Affiché au démarrage pendant la lecture du jeton et le chargement du profil.
class SplashScreen extends StatelessWidget {
  final String message;
  const SplashScreen({super.key, this.message = 'Chargement de votre espace…'});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Rcr.ink,
      body: Container(
        width: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(colors: [Rcr.ink, Rcr.ink2], begin: Alignment.topCenter, end: Alignment.bottomCenter),
        ),
        child: SafeArea(
          child: Column(children: [
            const Spacer(flex: 3),
            TweenAnimationBuilder<double>(
              tween: Tween(begin: 0.85, end: 1),
              duration: const Duration(milliseconds: 700),
              curve: Curves.easeOutBack,
              builder: (context, v, child) => Opacity(opacity: v.clamp(0.0, 1.0), child: Transform.scale(scale: v, child: child)),
              child: Image.asset('assets/logo.png', width: 148, height: 148),
            ),
            const SizedBox(height: 22),
            const Text('RCR', style: TextStyle(color: Colors.white, fontSize: 34, fontWeight: FontWeight.w800, letterSpacing: 6)),
            const SizedBox(height: 8),
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 32),
              child: Text('Rassemblement des Chrétiens Républicains',
                  textAlign: TextAlign.center, style: TextStyle(color: Color(0xFFD9CFB5), fontSize: 15, height: 1.3)),
            ),
            const SizedBox(height: 14),
            Container(width: 54, height: 3, decoration: BoxDecoration(color: Rcr.gold2, borderRadius: BorderRadius.circular(99))),
            const Spacer(flex: 2),
            const SizedBox(width: 30, height: 30, child: CircularProgressIndicator(strokeWidth: 3, color: Rcr.gold2)),
            const SizedBox(height: 14),
            Text(message, style: const TextStyle(color: Colors.white, fontSize: 14)),
            const SizedBox(height: 6),
            const Text('Ensemble, bâtissons la République', style: TextStyle(color: Rcr.gold2, fontSize: 12.5, fontStyle: FontStyle.italic)),
            const SizedBox(height: 28),
          ]),
        ),
      ),
    );
  }
}
