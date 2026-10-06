double _d(dynamic v) => v is num ? v.toDouble() : (double.tryParse('${v ?? ''}') ?? 0);
int? _i(dynamic v) => v is num ? v.toInt() : int.tryParse('${v ?? ''}');
String _s(dynamic v) => v == null ? '' : '$v';

class Membre {
  final int id;
  final String code, nom, postnom, prenom, email, telephone, categorie, grade, periode, province, statut;
  final String? dateEcheance, photoUrl;
  final int? joursRestants;
  final bool aJour, premierPaiementFait;
  final double? tarifMontant;
  final int? tarifMois, idCot;

  Membre({
    required this.id, required this.code, required this.nom, required this.postnom, required this.prenom, required this.email,
    required this.telephone, required this.categorie, required this.grade, required this.periode, required this.province,
    required this.statut, this.dateEcheance, this.photoUrl, this.joursRestants, required this.aJour,
    required this.premierPaiementFait, this.tarifMontant, this.tarifMois, this.idCot,
  });

  factory Membre.fromJson(Map<String, dynamic> j) {
    final t = j['tarif_actuel'] is Map ? j['tarif_actuel'] as Map : null;
    return Membre(
      id: _i(j['id']) ?? 0, code: _s(j['code']), nom: _s(j['nom']), postnom: _s(j['postnom']), prenom: _s(j['prenom']),
      email: _s(j['email']), telephone: _s(j['telephone']), categorie: _s(j['categorie']), grade: _s(j['grade']),
      periode: _s(j['periode']), province: _s(j['province']), statut: _s(j['statut']),
      dateEcheance: j['date_echeance']?.toString(), photoUrl: j['photo_url']?.toString(), joursRestants: _i(j['jours_restants']),
      aJour: j['a_jour'] == true, premierPaiementFait: j['premier_paiement_fait'] == true,
      tarifMontant: t == null ? null : _d(t['montant']), tarifMois: t == null ? null : _i(t['mois']), idCot: t == null ? null : _i(t['id_cot']),
    );
  }

  String get nomComplet => [prenom, nom, postnom].where((e) => e.isNotEmpty).join(' ');
}

class Grade {
  final int id;
  final String nom;
  final double prixMensuel;
  Grade(this.id, this.nom, this.prixMensuel);
}

class Categorie {
  final int id;
  final String nom;
  final List<Grade> grades;
  Categorie(this.id, this.nom, this.grades);
}

class Periode {
  final int id, mois;
  final String nom;
  Periode(this.id, this.nom, this.mois);
}

class Tarifs {
  final List<Categorie> categories;
  final List<Periode> periodes;
  Tarifs(this.categories, this.periodes);

  factory Tarifs.fromJson(Map<String, dynamic> j) => Tarifs(
        (j['categories'] as List? ?? [])
            .map((c) => Categorie(
                  _i(c['id']) ?? 0,
                  _s(c['nom']),
                  (c['grades'] as List? ?? []).map((g) => Grade(_i(g['id']) ?? 0, _s(g['nom']), _d(g['prix_mensuel']))).toList(),
                ))
            .toList(),
        (j['periodes'] as List? ?? []).map((p) => Periode(_i(p['id']) ?? 0, _s(p['nom']), _i(p['mois']) ?? 1)).toList(),
      );

  Grade? gradeParId(int id) {
    for (final c in categories) {
      for (final g in c.grades) {
        if (g.id == id) return g;
      }
    }
    return null;
  }
}

class Choix {
  final int id;
  final String nom;
  Choix(this.id, this.nom);
  static List<Choix> liste(dynamic items) => (items as List? ?? []).map((e) => Choix(_i(e['id']) ?? 0, _s(e['nom']))).toList();
}

class Paiement {
  final int id;
  final String reference, type, canal, statut, date;
  final double montant;
  final String devise;
  final String? periodeFin;
  Paiement({required this.id, required this.reference, required this.type, required this.canal, required this.statut,
      required this.date, required this.montant, required this.devise, this.periodeFin});

  factory Paiement.fromJson(Map<String, dynamic> j) => Paiement(
        id: _i(j['id']) ?? 0, reference: _s(j['reference']), type: _s(j['type']), canal: _s(j['canal']), statut: _s(j['statut']),
        date: _s(j['date']), montant: _d(j['montant']), devise: _s(j['devise']).isEmpty ? 'USD' : _s(j['devise']), periodeFin: j['periode_fin']?.toString(),
      );

  bool get paye => statut == 'paid';
}

class Don {
  final int id;
  final String reference, type, canal, statut, date;
  final String? frequence, prochaineEcheance;
  final double montant;
  final bool aRenouveler;
  Don({required this.id, required this.reference, required this.type, required this.canal, required this.statut, required this.date,
      required this.montant, this.frequence, this.prochaineEcheance, required this.aRenouveler});

  factory Don.fromJson(Map<String, dynamic> j) => Don(
        id: _i(j['id']) ?? 0, reference: _s(j['reference']), type: _s(j['type']), canal: _s(j['canal']), statut: _s(j['statut']),
        date: _s(j['date']), montant: _d(j['montant']), frequence: j['frequence']?.toString(),
        prochaineEcheance: j['prochaine_echeance']?.toString(), aRenouveler: j['a_renouveler'] == true,
      );

  bool get paye => statut == 'paid';
}

/// Cible d'un suivi de paiement (écran d'attente).
class Suivi {
  final bool don;
  final int id;
  final String? cle; // lien de suivi d'un don sans compte
  Suivi({required this.don, required this.id, this.cle});
}
