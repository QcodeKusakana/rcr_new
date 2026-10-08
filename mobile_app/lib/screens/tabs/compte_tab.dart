import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../api/api_client.dart';
import '../../models/models.dart';
import '../../state/session.dart';
import '../../theme.dart';
import '../../widgets/common.dart';
import '../password_screen.dart';
import '../pay_screen.dart';

/// « Mon compte » : reprend les rubriques de l'espace membre du site (identité, circonscriptions, abonnement,
/// parrainage, documents) puis la sécurité et la déconnexion.
class CompteTab extends StatefulWidget {
  const CompteTab({super.key});
  @override
  State<CompteTab> createState() => _CompteTabState();
}

class _CompteTabState extends State<CompteTab> {
  Profil? _profil;
  Parrainage? _parrainage;
  String? _erreur;
  bool _chargement = true;

  @override
  void initState() {
    super.initState();
    _charger();
  }

  Future<void> _charger() async {
    final s = context.read<Session>();
    try {
      final r = await Future.wait([s.api.get('/me/profil'), s.api.get('/me/parrainage')]);
      if (!mounted) return;
      setState(() {
        _profil = Profil.fromJson(r[0]['profil'] as Map<String, dynamic>);
        _parrainage = Parrainage.fromJson(r[1]);
        _erreur = null;
        _chargement = false;
      });
    } on ApiException catch (e) {
      if (mounted) setState(() { _erreur = e.message; _chargement = false; });
    }
  }

  Future<void> _ouvrir(String url) async {
    final ok = await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
    if (!ok && mounted) toast(context, "Impossible d'ouvrir le lien.", error: true);
  }

  Future<void> _deconnexion() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: const Text('Se déconnecter ?'),
        content: const Text('Vous devrez saisir à nouveau votre identifiant et votre mot de passe.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Déconnexion')),
        ],
      ),
    );
    if (ok == true && mounted) {
      await context.read<Session>().logout();
      if (mounted) toast(context, 'Vous êtes déconnecté.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = context.watch<Session>();
    final m = s.membre;
    if (m == null) return const SizedBox.shrink();

    return RefreshIndicator(
      onRefresh: () async {
        await s.refresh();
        await _charger();
      },
      child: ListView(padding: const EdgeInsets.all(16), physics: const AlwaysScrollableScrollPhysics(), children: [
        _entete(m),
        const SizedBox(height: 14),
        _abonnement(s, m),
        const SizedBox(height: 14),
        if (_chargement) const Padding(padding: EdgeInsets.all(24), child: Center(child: CircularProgressIndicator())),
        if (_erreur != null) ...[
          ErrorBox(_erreur!),
          OutlinedButton.icon(onPressed: _charger, icon: const Icon(Icons.refresh), label: const Text('Réessayer')),
          const SizedBox(height: 14),
        ],
        if (_profil != null) ...[
          _identite(_profil!),
          const SizedBox(height: 14),
          _circonscriptions(_profil!),
          const SizedBox(height: 14),
        ],
        if (_parrainage != null) ...[
          _parrainageCarte(_parrainage!),
          const SizedBox(height: 14),
        ],
        _documents(s, m),
        const SizedBox(height: 14),
        _securite(),
        const SizedBox(height: 18),
        Center(child: Text('RCR · application mobile', style: TextStyle(color: Rcr.soft.withValues(alpha: 0.7), fontSize: 12))),
      ]),
    );
  }

  Widget _entete(Membre m) => Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [Rcr.ink, Rcr.ink2], begin: Alignment.topLeft, end: Alignment.bottomRight),
          borderRadius: BorderRadius.circular(18),
        ),
        child: Row(children: [
          Container(
            padding: const EdgeInsets.all(3),
            decoration: const BoxDecoration(shape: BoxShape.circle, color: Rcr.gold2),
            child: CircleAvatar(
              radius: 36, backgroundColor: Rcr.paper2,
              backgroundImage: m.photoUrl == null ? null : NetworkImage(m.photoUrl!),
              onBackgroundImageError: m.photoUrl == null ? null : (_, __) {},
              child: m.photoUrl == null ? const Icon(Icons.person, color: Rcr.soft, size: 36) : null,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(m.nomComplet, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800)),
              const SizedBox(height: 4),
              Text(m.code, style: const TextStyle(color: Rcr.gold2, fontWeight: FontWeight.w700, letterSpacing: 1.1)),
              const SizedBox(height: 8),
              StatusChip(m.statut),
            ]),
          ),
        ]),
      );

  Widget _abonnement(Session s, Membre m) {
    final p = _profil;
    final premier = !m.premierPaiementFait;
    final bientot = m.aJour && (m.joursRestants ?? 999) <= 30;
    final (Color, String) etat = premier
        ? (Rcr.gold, "Adhésion en attente de paiement")
        : m.aJour
            ? (Rcr.green, 'Cotisation à jour jusqu\'au ${dateFr(m.dateEcheance)}')
            : (Rcr.red, 'Cotisation expirée depuis le ${dateFr(m.dateEcheance)}');
    return PanneauCarte(icone: Icons.workspace_premium_outlined, titre: 'Abonnement', enfants: [
      Container(
        margin: const EdgeInsets.only(top: 6, bottom: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(color: etat.$1.withValues(alpha: 0.1), border: Border(left: BorderSide(color: etat.$1, width: 4))),
        child: Text(etat.$2, style: TextStyle(color: etat.$1, fontWeight: FontWeight.w700)),
      ),
      InfoRow('Catégorie', m.categorie),
      InfoRow('Grade', m.grade),
      InfoRow('Période', m.periode),
      if (p != null) InfoRow('Tarif du grade', '${money(p.prixMensuel, p.devise)} / mois × ${p.mois} mois'),
      if (m.tarifMontant != null) InfoRow(premier ? 'Montant à payer' : 'Renouvellement', money(m.tarifMontant!), fort: true),
      if (m.joursRestants != null && !premier)
        InfoRow('Jours restants', m.joursRestants! >= 0 ? '${m.joursRestants}' : 'Échue depuis ${-m.joursRestants!} j'),
      const SizedBox(height: 10),
      if (premier || !m.aJour || bientot)
        FilledButton.icon(
          style: FilledButton.styleFrom(backgroundColor: Rcr.gold2, foregroundColor: Rcr.ink),
          onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PayScreen())),
          icon: Icon(premier ? Icons.lock_outline : Icons.autorenew),
          label: Text(premier ? 'Payer mon adhésion' : (m.aJour ? 'Renouveler par anticipation' : 'Renouveler ma cotisation')),
        ),
    ]);
  }

  Widget _identite(Profil p) => PanneauCarte(icone: Icons.badge_outlined, titre: 'Identité', enfants: [
        InfoRow('Code', p.code, fort: true),
        InfoRow('Civilité', p.civilite),
        InfoRow('Nom', p.nom),
        InfoRow('Post-nom', p.postnom),
        InfoRow('Prénom', p.prenom),
        InfoRow('Naissance', dateFr(p.dateNaissance)),
        InfoRow('Téléphone', p.telephone),
        InfoRow('E-mail', p.email),
        InfoRow('Adresse', [p.adresse, p.ville].where((e) => e.isNotEmpty).join(', ')),
        InfoRow("Membre depuis", dateFr(p.dateAdhesion)),
      ]);

  Widget _circonscriptions(Profil p) => PanneauCarte(icone: Icons.place_outlined, titre: 'Circonscriptions', enfants: [
        InfoRow('Pays', p.nationalite),
        InfoRow('Province', p.province),
        InfoRow('Territoire', p.territoire),
        InfoRow('Secteur', p.secteur),
        InfoRow('Qualité', p.categorie),
        InfoRow('Grade', p.grade),
      ]);

  Widget _parrainageCarte(Parrainage pa) {
    return PanneauCarte(icone: Icons.groups_outlined, titre: 'Parrainage', enfants: [
      const SizedBox(height: 6),
      const Text('Partagez votre lien personnel : chaque adhésion réalisée depuis ce lien vous est automatiquement associée.',
          style: TextStyle(color: Rcr.soft, fontSize: 13.5)),
      const SizedBox(height: 10),
      Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(color: Rcr.paper.withValues(alpha: 0.6), border: Border.all(color: Rcr.line), borderRadius: BorderRadius.circular(10)),
        child: SelectableText(pa.lien, style: const TextStyle(fontSize: 13, color: Rcr.ink)),
      ),
      const SizedBox(height: 10),
      Row(children: [
        Expanded(
          child: OutlinedButton.icon(
            onPressed: () async {
              await Clipboard.setData(ClipboardData(text: pa.lien));
              if (mounted) toast(context, 'Lien copié.');
            },
            icon: const Icon(Icons.copy, size: 18), label: const Text('Copier'),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: FilledButton.icon(
            style: FilledButton.styleFrom(backgroundColor: const Color(0xFF25D366), foregroundColor: Colors.white),
            onPressed: () => _ouvrir('https://wa.me/?text=${Uri.encodeComponent(pa.message)}'),
            icon: const Icon(Icons.send, size: 18), label: const Text('WhatsApp'),
          ),
        ),
      ]),
      const SizedBox(height: 14),
      Row(children: [
        Expanded(child: _stat('${pa.filleuls.length}', 'Filleul(s)')),
        const SizedBox(width: 10),
        Expanded(child: _stat(money(pa.commissionTotale), 'Commission estimée (${pa.taux.toStringAsFixed(0)} %)')),
      ]),
      const SizedBox(height: 8),
      if (pa.filleuls.isEmpty)
        const Padding(
          padding: EdgeInsets.symmetric(vertical: 8),
          child: Text("Vous n'avez encore parrainé personne.", style: TextStyle(color: Rcr.soft)),
        )
      else ...[
        const Divider(),
        for (final f in pa.filleuls)
          ListTile(
            contentPadding: EdgeInsets.zero, dense: true,
            title: Text(f.nom, style: const TextStyle(fontWeight: FontWeight.w700)),
            subtitle: Text('${f.code} · ${f.grade} · ${dateFr(f.dateAdhesion)}\n${f.nbPaiements} paiement(s) · ${money(f.totalPaye)}'),
            isThreeLine: true,
            trailing: Text(money(f.commission), style: const TextStyle(color: Rcr.green, fontWeight: FontWeight.w800)),
          ),
        const SizedBox(height: 4),
        const Text("Estimation indicative : aucun versement n'est déclenché automatiquement.", style: TextStyle(color: Rcr.soft, fontSize: 12)),
      ],
    ]);
  }

  Widget _stat(String valeur, String libelle) => Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(color: Rcr.ink, borderRadius: BorderRadius.circular(12)),
        child: Column(children: [
          Text(valeur, style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800)),
          const SizedBox(height: 2),
          Text(libelle, textAlign: TextAlign.center, style: const TextStyle(color: Color(0xFFD9CFB5), fontSize: 11.5)),
        ]),
      );

  Widget _documents(Session s, Membre m) => PanneauCarte(icone: Icons.folder_open_outlined, titre: 'Mes documents', enfants: [
        const SizedBox(height: 6),
        OutlinedButton.icon(
          onPressed: () => ouvrirPdf(context, () => s.api.download('/me/fiche'), 'fiche_adhesion_${m.code}'),
          icon: const Icon(Icons.description_outlined), label: const Text("Fiche d'adhésion (PDF)"),
        ),
        const SizedBox(height: 10),
        OutlinedButton.icon(
          onPressed: m.aJour
              ? () => ouvrirPdf(context, () => s.api.download('/me/carte'), 'carte_membre_${m.code}')
              : () => toast(context, 'Votre carte est disponible dès que votre cotisation est à jour.'),
          icon: const Icon(Icons.badge_outlined), label: const Text('Carte de membre (PDF)'),
        ),
      ]);

  Widget _securite() => PanneauCarte(icone: Icons.shield_outlined, titre: 'Sécurité et aide', enfants: [
        const SizedBox(height: 6),
        OutlinedButton.icon(
          onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PasswordScreen())),
          icon: const Icon(Icons.lock_reset), label: const Text('Changer mon mot de passe'),
        ),
        const SizedBox(height: 10),
        OutlinedButton.icon(
          onPressed: () => _ouvrir('https://rcr.cd'),
          icon: const Icon(Icons.public), label: const Text('Site du RCR'),
        ),
        const SizedBox(height: 10),
        FilledButton.icon(
          style: FilledButton.styleFrom(backgroundColor: Rcr.red),
          onPressed: _deconnexion,
          icon: const Icon(Icons.logout), label: const Text('Se déconnecter'),
        ),
      ]);
}
