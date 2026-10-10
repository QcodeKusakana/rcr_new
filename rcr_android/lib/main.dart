import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';

import 'screens/home_screen.dart';
import 'screens/login_screen.dart';
import 'screens/splash_screen.dart';
import 'state/session.dart';
import 'theme.dart';
import 'widgets/navigation.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('fr_FR');
  runApp(ChangeNotifierProvider(create: (_) => Session()..init(), child: const RcrApp()));
}

class RcrApp extends StatelessWidget {
  const RcrApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'RCR',
      debugShowCheckedModeBanner: false,
      theme: Rcr.theme(),
      locale: const Locale('fr', 'FR'),
      supportedLocales: const [Locale('fr', 'FR')],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      home: const _Racine(),
    );
  }
}

/// Aiguillage selon l'état de la session. L'écran de chargement reste visible au moins 1,2 s
/// (lecture du jeton, chargement du profil) pour éviter un clignotement.
class _Racine extends StatefulWidget {
  const _Racine();
  @override
  State<_Racine> createState() => _RacineState();
}

class _RacineState extends State<_Racine> {
  bool _minimumEcoule = false;

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 1200), () {
      if (mounted) setState(() => _minimumEcoule = true);
    });
  }

  @override
  Widget build(BuildContext context) {
    final s = context.watch<Session>();
    if (s.status == AuthStatus.loading || !_minimumEcoule) {
      return const SplashScreen();
    }
    switch (s.status) {
      case AuthStatus.loading:
        return const SplashScreen();
      case AuthStatus.loggedIn:
        return const HomeScreen();
      case AuthStatus.offline:
        return DoubleRetourQuitter(child: Scaffold(
          appBar: rcrAppBar('Hors connexion', automaticallyImplyLeading: false),
          body: Center(
            child: Padding(
              padding: const EdgeInsets.all(28),
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(Icons.wifi_off, size: 56, color: Rcr.soft),
                const SizedBox(height: 14),
                const Text('Connexion impossible', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700)),
                const SizedBox(height: 8),
                const Text('Vérifiez votre accès Internet puis réessayez.', textAlign: TextAlign.center),
                const SizedBox(height: 20),
                FilledButton(onPressed: () => context.read<Session>().init(), child: const Text('Réessayer')),
              ]),
            ),
          ),
        ));
      case AuthStatus.loggedOut:
        return const LoginScreen();
    }
  }
}
