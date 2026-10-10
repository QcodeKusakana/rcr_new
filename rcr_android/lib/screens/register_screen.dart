import 'dart:io';

import 'package:flutter/material.dart';
import '../widgets/navigation.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../api/api_client.dart';
import '../models/models.dart';
import '../state/session.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'pay_screen.dart';

/// Adhésion en 4 étapes (mêmes champs et mêmes règles que le formulaire du site).
class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _forms = List.generate(4, (_) => GlobalKey<FormState>());
  int _etape = 0;
  bool _chargement = true, _envoi = false;
  String? _erreur, _erreurChargement;

  Tarifs? _tarifs;
  List<Choix> _provinces = [], _territoires = [], _secteurs = [];

  Categorie? _cat;
  Grade? _grade;
  Periode? _periode;
  String? _civilite, _sexe, _nationalite = 'CD';
  Choix? _province, _territoire, _secteur;
  DateTime? _naissance;
  File? _photo;
  bool _accepte = false, _voirMdp = false;

  final _nom = TextEditingController(), _postnom = TextEditingController(), _prenom = TextEditingController();
  final _email = TextEditingController(), _tel = TextEditingController(), _ville = TextEditingController();
  final _adresse = TextEditingController(), _diplome = TextEditingController();
  final _mdp = TextEditingController(), _mdp2 = TextEditingController();

  @override
  void initState() {
    super.initState();
    _charger();
  }

  @override
  void dispose() {
    for (final c in [_nom, _postnom, _prenom, _email, _tel, _ville, _adresse, _diplome, _mdp, _mdp2]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _charger() async {
    final s = context.read<Session>();
    try {
      final t = await s.tarifs(forcer: true);
      final p = await s.api.get('/localisation/provinces');
      if (!mounted) return;
      setState(() {
        _tarifs = t;
        _provinces = Choix.liste(p['items']);
        _chargement = false;
      });
    } on ApiException catch (e) {
      if (mounted) setState(() { _erreurChargement = e.message; _chargement = false; });
    }
  }

  Future<void> _chargerTerritoires(Choix p) async {
    setState(() { _province = p; _territoire = null; _secteur = null; _territoires = []; _secteurs = []; });
    try {
      final r = await context.read<Session>().api.get('/localisation/territoires', query: {'province': '${p.id}'});
      if (mounted) setState(() => _territoires = Choix.liste(r['items']));
    } on ApiException catch (e) {
      if (mounted) setState(() => _erreur = e.message);
    }
  }

  Future<void> _chargerSecteurs(Choix t) async {
    setState(() { _territoire = t; _secteur = null; _secteurs = []; });
    try {
      final r = await context.read<Session>().api.get('/localisation/secteurs', query: {'territoire': '${t.id}'});
      if (mounted) setState(() => _secteurs = Choix.liste(r['items']));
    } on ApiException catch (e) {
      if (mounted) setState(() => _erreur = e.message);
    }
  }

  Future<void> _choisirPhoto(ImageSource src) async {
    final x = await ImagePicker().pickImage(source: src, maxWidth: 1200, imageQuality: 85);
    if (x != null && mounted) setState(() => _photo = File(x.path));
  }

  double? get _montant => (_grade == null || _periode == null) ? null : _grade!.prixMensuel * _periode!.mois;

  bool _valider(int i) {
    if (!_forms[i].currentState!.validate()) return false;
    if (i == 0 && (_cat == null || _grade == null || _periode == null)) {
      setState(() => _erreur = 'Choisissez une catégorie, un grade et une période.');
      return false;
    }
    if (i == 3 && _photo == null) {
      setState(() => _erreur = 'Une photo est obligatoire.');
      return false;
    }
    if (i == 3 && !_accepte) {
      setState(() => _erreur = 'Vous devez accepter les mentions légales et la politique de confidentialité.');
      return false;
    }
    setState(() => _erreur = null);
    return true;
  }

  Future<void> _envoyer() async {
    setState(() { _envoi = true; _erreur = null; });
    final s = context.read<Session>();
    try {
      final r = await s.api.multipart('/adhesion', {
        'nom': _nom.text.trim(), 'postnom': _postnom.text.trim(), 'prenom': _prenom.text.trim(),
        'email': _email.text.trim(), 'telephone': _tel.text.trim(), 'civilite': _civilite ?? '', 'sexe': _sexe ?? '',
        'nationalite': _nationalite ?? '', 'date_naissance': DateFormat('yyyy-MM-dd').format(_naissance!),
        'adresse': _adresse.text.trim(), 'ville': _ville.text.trim(), 'diplome': _diplome.text.trim(),
        'id_categorie': '${_cat!.id}', 'id_grade': '${_grade!.id}', 'id_periode': '${_periode!.id}',
        'id_province': '${_province!.id}', 'id_territoire': '${_territoire!.id}', 'id_secteur': '${_secteur!.id}',
        'mot_de_passe': _mdp.text, 'acceptation_legale': 'true', 'appareil': Platform.operatingSystem,
      }, files: {'photo': _photo!});
      await s.ouvrirDepuisReponse(r);
      if (!mounted) return;
      // Le compte existe : on passe directement au paiement de l'adhésion
      Navigator.of(context).popUntil((r) => r.isFirst);
      Navigator.push(context, MaterialPageRoute(builder: (_) => const PayScreen()));
    } on ApiException catch (e) {
      if (mounted) setState(() => _erreur = e.message);
    } finally {
      if (mounted) setState(() => _envoi = false);
    }
  }

  String? _req(String? v) => (v == null || v.trim().isEmpty) ? 'Champ obligatoire' : null;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: rcrAppBar('Adhérer au RCR'),
      body: _chargement
          ? const Center(child: CircularProgressIndicator())
          : _erreurChargement != null
              ? Center(child: Padding(padding: const EdgeInsets.all(24), child: Column(mainAxisSize: MainAxisSize.min, children: [
                  ErrorBox(_erreurChargement!),
                  FilledButton(onPressed: () { setState(() { _chargement = true; _erreurChargement = null; }); _charger(); }, child: const Text('Réessayer')),
                ])))
              : Column(children: [
                  if (_erreur != null) Padding(padding: const EdgeInsets.fromLTRB(16, 12, 16, 0), child: ErrorBox(_erreur!)),
                  Expanded(
                    child: Stepper(
                      type: StepperType.vertical,
                      currentStep: _etape,
                      onStepTapped: (i) { if (i < _etape) setState(() => _etape = i); },
                      controlsBuilder: (context, d) => Padding(
                        padding: const EdgeInsets.only(top: 16),
                        child: Row(children: [
                          Expanded(
                            child: _etape == 3
                                ? BusyButton(busy: _envoi, label: 'Valider et payer', icon: Icons.lock_outline, onPressed: () { if (_valider(3)) _envoyer(); })
                                : FilledButton(onPressed: () { if (_valider(_etape)) setState(() => _etape++); }, child: const Text('Continuer')),
                          ),
                          if (_etape > 0) ...[
                            const SizedBox(width: 10),
                            TextButton(onPressed: _envoi ? null : () => setState(() => _etape--), child: const Text('Retour')),
                          ],
                        ]),
                      ),
                      steps: [
                        Step(title: const Text('Mon adhésion'), isActive: _etape >= 0, state: _etape > 0 ? StepState.complete : StepState.indexed, content: _etapeAdhesion()),
                        Step(title: const Text('Mon identité'), isActive: _etape >= 1, state: _etape > 1 ? StepState.complete : StepState.indexed, content: _etapeIdentite()),
                        Step(title: const Text('Ma localisation'), isActive: _etape >= 2, state: _etape > 2 ? StepState.complete : StepState.indexed, content: _etapeLocalisation()),
                        Step(title: const Text('Photo et sécurité'), isActive: _etape >= 3, content: _etapeSecurite()),
                      ],
                    ),
                  ),
                ]),
    );
  }

  Widget _etapeAdhesion() {
    final t = _tarifs!;
    return Form(
      key: _forms[0],
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        DropdownButtonFormField<Categorie>(
          value: _cat, isExpanded: true,
          decoration: const InputDecoration(labelText: 'Catégorie'),
          items: t.categories.map((c) => DropdownMenuItem(value: c, child: Text(c.nom))).toList(),
          onChanged: (c) => setState(() { _cat = c; _grade = null; }),
        ),
        const SizedBox(height: 12),
        DropdownButtonFormField<Grade>(
          key: ValueKey('grade-${_cat?.id}'),
          value: _grade, isExpanded: true,
          decoration: const InputDecoration(labelText: 'Grade'),
          items: (_cat?.grades ?? []).map((g) => DropdownMenuItem(value: g, child: Text('${g.nom} — ${money(g.prixMensuel)}/mois'))).toList(),
          onChanged: (g) => setState(() => _grade = g),
        ),
        const SizedBox(height: 12),
        DropdownButtonFormField<Periode>(
          value: _periode, isExpanded: true,
          decoration: const InputDecoration(labelText: 'Mode de cotisation'),
          items: t.periodes.map((p) => DropdownMenuItem(value: p, child: Text(p.nom))).toList(),
          onChanged: (p) => setState(() => _periode = p),
        ),
        if (_montant != null) ...[
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(color: Rcr.paper2, borderRadius: BorderRadius.circular(12)),
            child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Montant à payer', style: TextStyle(fontWeight: FontWeight.w600)),
              Text(money(_montant!), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: Rcr.ink)),
            ]),
          ),
        ],
      ]),
    );
  }

  Widget _etapeIdentite() {
    return Form(
      key: _forms[1],
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        DropdownButtonFormField<String>(
          value: _civilite, decoration: const InputDecoration(labelText: 'Civilité'),
          items: const ['Mr', 'Mme', 'Mlle'].map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
          onChanged: (v) => setState(() => _civilite = v), validator: (v) => v == null ? 'Champ obligatoire' : null,
        ),
        const SizedBox(height: 12),
        TextFormField(controller: _nom, textCapitalization: TextCapitalization.words, decoration: const InputDecoration(labelText: 'Nom'), validator: _req),
        const SizedBox(height: 12),
        TextFormField(controller: _postnom, textCapitalization: TextCapitalization.words, decoration: const InputDecoration(labelText: 'Post-nom'), validator: _req),
        const SizedBox(height: 12),
        TextFormField(controller: _prenom, textCapitalization: TextCapitalization.words, decoration: const InputDecoration(labelText: 'Prénom'), validator: _req),
        const SizedBox(height: 12),
        DropdownButtonFormField<String>(
          value: _sexe, decoration: const InputDecoration(labelText: 'Sexe'),
          items: const [DropdownMenuItem(value: 'M', child: Text('Masculin')), DropdownMenuItem(value: 'F', child: Text('Féminin'))],
          onChanged: (v) => setState(() => _sexe = v), validator: (v) => v == null ? 'Champ obligatoire' : null,
        ),
        const SizedBox(height: 12),
        FormField<DateTime>(
          validator: (_) => _naissance == null ? 'Champ obligatoire' : null,
          builder: (st) => InkWell(
            onTap: () async {
              final d = await showDatePicker(
                context: context, initialDate: _naissance ?? DateTime(1990), firstDate: DateTime(1920), lastDate: DateTime.now(),
              );
              if (d != null) { setState(() => _naissance = d); st.didChange(d); }
            },
            child: InputDecorator(
              decoration: InputDecoration(labelText: 'Date de naissance', errorText: st.errorText, suffixIcon: const Icon(Icons.calendar_today_outlined)),
              child: Text(_naissance == null ? 'Sélectionner' : DateFormat('d MMMM yyyy', 'fr_FR').format(_naissance!)),
            ),
          ),
        ),
        const SizedBox(height: 12),
        DropdownButtonFormField<String>(
          value: _nationalite, decoration: const InputDecoration(labelText: 'Nationalité'),
          items: const [
            DropdownMenuItem(value: 'CD', child: Text('Congo-Kinshasa')), DropdownMenuItem(value: 'FR', child: Text('France')),
            DropdownMenuItem(value: 'BE', child: Text('Belgique')), DropdownMenuItem(value: 'US', child: Text('États-Unis')),
          ],
          onChanged: (v) => setState(() => _nationalite = v),
        ),
        const SizedBox(height: 12),
        TextFormField(
          controller: _email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Adresse e-mail'),
          validator: (v) => RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch((v ?? '').trim()) ? null : 'Adresse e-mail invalide',
        ),
        const SizedBox(height: 12),
        TextFormField(
          controller: _tel, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Téléphone', hintText: '0812345678'),
          validator: (v) => RegExp(r'^\+?[0-9 ]{9,16}$').hasMatch((v ?? '').trim()) ? null : 'Numéro invalide',
        ),
      ]),
    );
  }

  Widget _etapeLocalisation() {
    return Form(
      key: _forms[2],
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        DropdownButtonFormField<Choix>(
          value: _province, isExpanded: true, decoration: const InputDecoration(labelText: 'Province'),
          items: _provinces.map((p) => DropdownMenuItem(value: p, child: Text(p.nom))).toList(),
          onChanged: (p) { if (p != null) _chargerTerritoires(p); }, validator: (v) => v == null ? 'Champ obligatoire' : null,
        ),
        const SizedBox(height: 12),
        DropdownButtonFormField<Choix>(
          key: ValueKey('terr-${_province?.id}'),
          value: _territoire, isExpanded: true, decoration: const InputDecoration(labelText: 'Territoire'),
          items: _territoires.map((p) => DropdownMenuItem(value: p, child: Text(p.nom))).toList(),
          onChanged: (p) { if (p != null) _chargerSecteurs(p); }, validator: (v) => v == null ? 'Champ obligatoire' : null,
        ),
        const SizedBox(height: 12),
        DropdownButtonFormField<Choix>(
          key: ValueKey('sect-${_territoire?.id}'),
          value: _secteur, isExpanded: true, decoration: const InputDecoration(labelText: 'Secteur'),
          items: _secteurs.map((p) => DropdownMenuItem(value: p, child: Text(p.nom))).toList(),
          onChanged: (p) => setState(() => _secteur = p), validator: (v) => v == null ? 'Champ obligatoire' : null,
        ),
        const SizedBox(height: 12),
        TextFormField(controller: _ville, decoration: const InputDecoration(labelText: 'Ville')),
        const SizedBox(height: 12),
        TextFormField(controller: _adresse, maxLines: 2, decoration: const InputDecoration(labelText: 'Adresse'), validator: _req),
      ]),
    );
  }

  Widget _etapeSecurite() {
    return Form(
      key: _forms[3],
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        Center(
          child: CircleAvatar(
            radius: 52, backgroundColor: Rcr.paper2,
            backgroundImage: _photo == null ? null : FileImage(_photo!),
            child: _photo == null ? const Icon(Icons.person, size: 48, color: Rcr.soft) : null,
          ),
        ),
        const SizedBox(height: 10),
        Row(children: [
          Expanded(child: OutlinedButton.icon(onPressed: () => _choisirPhoto(ImageSource.camera), icon: const Icon(Icons.photo_camera_outlined), label: const Text('Photo'))),
          const SizedBox(width: 10),
          Expanded(child: OutlinedButton.icon(onPressed: () => _choisirPhoto(ImageSource.gallery), icon: const Icon(Icons.photo_library_outlined), label: const Text('Galerie'))),
        ]),
        const SizedBox(height: 14),
        TextFormField(controller: _diplome, decoration: const InputDecoration(labelText: 'Diplôme (facultatif)')),
        const SizedBox(height: 12),
        TextFormField(
          controller: _mdp, obscureText: !_voirMdp,
          decoration: InputDecoration(
            labelText: 'Mot de passe (8 caractères minimum)',
            suffixIcon: IconButton(icon: Icon(_voirMdp ? Icons.visibility_off : Icons.visibility), onPressed: () => setState(() => _voirMdp = !_voirMdp)),
          ),
          validator: (v) => (v ?? '').length < 8 ? '8 caractères minimum' : null,
        ),
        const SizedBox(height: 12),
        TextFormField(
          controller: _mdp2, obscureText: !_voirMdp, decoration: const InputDecoration(labelText: 'Confirmer le mot de passe'),
          validator: (v) => v != _mdp.text ? 'Les mots de passe ne correspondent pas' : null,
        ),
        const SizedBox(height: 8),
        CheckboxListTile(
          value: _accepte, onChanged: (v) => setState(() => _accepte = v ?? false), contentPadding: EdgeInsets.zero, controlAffinity: ListTileControlAffinity.leading,
          title: Wrap(children: [
            const Text("J'accepte les "),
            GestureDetector(
              onTap: () => launchUrl(Uri.parse('https://rcr.cd/index.php?pages=mention'), mode: LaunchMode.externalApplication),
              child: const Text('mentions légales', style: TextStyle(decoration: TextDecoration.underline, color: Rcr.gold)),
            ),
            const Text(' et la '),
            GestureDetector(
              onTap: () => launchUrl(Uri.parse('https://rcr.cd/index.php?pages=politique-confidentialite'), mode: LaunchMode.externalApplication),
              child: const Text('politique de confidentialité', style: TextStyle(decoration: TextDecoration.underline, color: Rcr.gold)),
            ),
          ]),
        ),
      ]),
    );
  }
}
