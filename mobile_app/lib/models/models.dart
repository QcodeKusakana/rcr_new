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

/// Identité, circonscriptions et abonnement (GET /me/profil) : mêmes informations que l'espace membre du site.
class Profil {
  final String code, nom, postnom, prenom, email, telephone, civilite, nationalite, adresse, ville;
  final String province, territoire, secteur, categorie, grade, periode, devise;
  final String? dateNaissance, dateAdhesion;
  final double prixMensuel, totalPeriode;
  final int mois;

  Profil({
    required this.code, required this.nom, required this.postnom, required this.prenom, required this.email, required this.telephone,
    required this.civilite, required this.nationalite, required this.adresse, required this.ville, required this.province,
    required this.territoire, required this.secteur, required this.categorie, required this.grade, required this.periode,
    required this.devise, this.dateNaissance, this.dateAdhesion, required this.prixMensuel, required this.totalPeriode, required this.mois,
  });

  factory Profil.fromJson(Map<String, dynamic> j) => Profil(
        code: _s(j['code']), nom: _s(j['nom']), postnom: _s(j['postnom']), prenom: _s(j['prenom']), email: _s(j['email']),
        telephone: _s(j['telephone']), civilite: _s(j['civilite']), nationalite: _s(j['nationalite']), adresse: _s(j['adresse']),
        ville: _s(j['ville']), province: _s(j['province']), territoire: _s(j['territoire']), secteur: _s(j['secteur']),
        categorie: _s(j['categorie']), grade: _s(j['grade']), periode: _s(j['periode']),
        devise: _s(j['devise']).isEmpty ? 'USD' : _s(j['devise']),
        dateNaissance: j['date_naissance']?.toString(), dateAdhesion: j['date_adhesion']?.toString(),
        prixMensuel: _d(j['prix_mensuel']), totalPeriode: _d(j['total_periode']), mois: _i(j['mois']) ?? 1,
      );
}

class Filleul {
  final String nom, code, grade;
  final String? dateAdhesion;
  final int nbPaiements;
  final double totalPaye, commission;
  Filleul({required this.nom, required this.code, required this.grade, this.dateAdhesion, required this.nbPaiements,
      required this.totalPaye, required this.commission});

  factory Filleul.fromJson(Map<String, dynamic> j) => Filleul(
        nom: _s(j['nom']), code: _s(j['code']), grade: _s(j['grade']), dateAdhesion: j['date_adhesion']?.toString(),
        nbPaiements: _i(j['nb_paiements']) ?? 0, totalPaye: _d(j['total_paye']), commission: _d(j['commission']),
      );
}

/// Programme de parrainage (GET /me/parrainage) : lecture seule, aucun versement n'est déclenché par l'application.
class Parrainage {
  final String lien, message;
  final double taux, commissionTotale;
  final List<Filleul> filleuls;
  Parrainage({required this.lien, required this.message, required this.taux, required this.commissionTotale, required this.filleuls});

  factory Parrainage.fromJson(Map<String, dynamic> j) => Parrainage(
        lien: _s(j['lien']), message: _s(j['message']), taux: _d(j['taux']), commissionTotale: _d(j['commission_totale']),
        filleuls: (j['filleuls'] as List? ?? []).map((e) => Filleul.fromJson(e as Map<String, dynamic>)).toList(),
      );
}
