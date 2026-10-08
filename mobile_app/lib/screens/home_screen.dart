import 'package:flutter/material.dart';

import '../theme.dart';
import 'tabs/accueil_tab.dart';
import 'tabs/compte_tab.dart';
import 'tabs/dons_tab.dart';
import 'tabs/paiements_tab.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _i = 0;
  static const _titres = ['Mon espace', 'Mes paiements', 'Mes dons', 'Mon compte'];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: const PreferredSize(preferredSize: Size.fromHeight(3), child: ColoredBox(color: Rcr.gold2, child: SizedBox(height: 3, width: double.infinity))),
        title: Row(children: [
          Image.asset('assets/logo.png', height: 30),
          const SizedBox(width: 10),
          Text(_titres[_i]),
        ]),
      ),
      body: IndexedStack(index: _i, children: [
        AccueilTab(onAller: (n) => setState(() => _i = n)),
        // Les onglets rechargent leurs données à l'affichage
        _i == 1 ? const PaiementsTab() : const SizedBox.shrink(),
        _i == 2 ? const DonsTab() : const SizedBox.shrink(),
        const CompteTab(),
      ]),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _i,
        onDestinationSelected: (v) => setState(() => _i = v),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home, color: Rcr.ink), label: 'Accueil'),
          NavigationDestination(icon: Icon(Icons.receipt_long_outlined), selectedIcon: Icon(Icons.receipt_long, color: Rcr.ink), label: 'Paiements'),
          NavigationDestination(icon: Icon(Icons.favorite_border), selectedIcon: Icon(Icons.favorite, color: Rcr.ink), label: 'Dons'),
          NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person, color: Rcr.ink), label: 'Compte'),
        ],
      ),
    );
  }
}
