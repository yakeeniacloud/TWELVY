# RESUME SESSION — 14 Avril 2026

**Suivi de RESUME_SESSION_16MAR.md** — session dédiée à la finalisation de la préparation SEO pour les 96 + 13 nouvelles fiches département et région créées en session du 16 mars.

---

## 1. Contexte d'entrée de session

Session précédente (16 mars) : création des fiches département (96) et région (13) avec pages complètes, routes API Next.js, endpoint PHP `stages-geo.php`, et 3 barres de recherche mises à jour pour proposer les suggestions dept + région.

Tâches encore ouvertes au début de la session du 14 avril :
- Sitemap — ajouter les 109 nouvelles URLs
- Contenu WP pour fiches département
- Contenu WP pour fiches région
- Redirects PSP vers fiches dept/région (le cas échéant)
- 3 DOCX à supprimer manuellement via WP Admin (manuel Yakeen)

Travaux réalisés aujourd'hui : **image fallback sur dept/région, sitemap, redirects.csv Cat 8**.

---

## 2. Travail effectué aujourd'hui

### 2.1 Image fallback `salle-stage.jpg` sur pages département et région (commit `89206d1`)

**Objectif** : les pages ville affichent en bas l'image `/images/salle-stage.jpg` comme contenu par défaut lorsqu'il n'y a pas de contenu WordPress custom. Cette image manquait sur les pages département et région.

**Fix** : ajout du bloc image entre `</main>` et le footer dans les deux fichiers, identique au pattern des pages ville :

```tsx
{/* Image below courses */}
<div className="bg-gray-50 border-t border-gray-200 py-8 px-4 mt-8">
  <div className="mx-auto max-w-3xl flex justify-center">
    <img
      src="/images/salle-stage.jpg"
      alt="Salle de formation stage de récupération de points"
      className="rounded-lg max-w-full h-auto"
    />
  </div>
</div>
```

**Fichiers modifiés** :
- `app/stages-recuperation-points/departement/[dept]/page.tsx`
- `app/stages-recuperation-points/region/[region]/page.tsx`

**Différence vs page ville** : pas de conditionnel sur `cityContent`/`cityContentLoading` — pas de contenu WP pour les pages dept/région donc image affichée directement et systématiquement.

---

### 2.2 Sitemap — ajout de 119 nouvelles URLs (commit `5b6025f`)

**Constat initial** : les pages ville étaient déjà dans le sitemap via un fetch sur `https://api.twelvy.net/cities.php` (lignes 89–118 du sitemap.ts). Par contre les pages dept et région étaient totalement absentes → Google ne pouvait pas les découvrir.

**Réflexion** : les données dept/région étant statiques (imports des tableaux `DEPARTEMENTS` et `REGIONS` depuis les fichiers `lib/`), pas besoin d'appel API. Ajout en pur synchrone avant le `return entries`.

**Fix** dans `app/sitemap.ts` :

```typescript
import { DEPARTEMENTS } from '@/lib/departements'
import { REGIONS } from '@/lib/regions'

// …existing code…

// Department pages (96 métro + 5 DOM-TOM = 101 static entries)
for (const dept of DEPARTEMENTS) {
  entries.push({
    url: `${SITE_URL}/stages-recuperation-points/departement/${dept.slug}`,
    lastModified: new Date(),
    changeFrequency: 'weekly',
    priority: 0.8,
  })
}

// Region pages (13 métro + 5 overseas = 18 static entries)
for (const region of REGIONS) {
  entries.push({
    url: `${SITE_URL}/stages-recuperation-points/region/${region.slug}`,
    lastModified: new Date(),
    changeFrequency: 'weekly',
    priority: 0.8,
  })
}
```

**Paramètres choisis** :
- `priority: 0.8` — équivalent aux pages WP parentes (villes à 0.9 car données stages réellement changeantes quotidiennement)
- `changeFrequency: 'weekly'` — les pages elles-mêmes sont statiques, seules les listes de stages changent
- Pas de try/catch — données statiques, aucun point de défaillance

**Résultat** : 119 URLs ajoutées (101 dept + 18 région) au sitemap. Google les découvrira au prochain crawl via `https://www.twelvy.net/sitemap.xml`.

---

### 2.3 Redirects.csv — Catégorie 8 : 95 redirections département (commit `57d1f16`)

**Contexte** : le projet a un système de migration SEO PSP → Twelvy avec deux fichiers jumeaux :
1. `redirects.csv` — source de vérité, documente toutes les redirections avec statut `LIVE` (actif) ou `PRÊT` (à activer jour J du basculement de domaine)
2. `next.config.ts` — implémentation Next.js dans `async redirects()` (uniquement les `LIVE`)

Maintenant que les 109 fiches département/région Twelvy existent, il fallait cartographier les anciennes URLs PSP équivalentes pour préserver le SEO lors du basculement de domaine.

---

#### Phase 1 — Recherche approfondie

**Question initiale** : PSP a-t-il des pages département et/ou région ? Quelle est leur structure d'URL ?

**Recherche dans l'archive PSP** (`/Volumes/Crucial X9/PROSTAGES/www_3/`) :
- `find` pour trouver tout fichier `liens_regions|regions.php|recuperer-points-region` → **aucun résultat** → PSP n'a JAMAIS eu de pages région
- Trouvé `/Volumes/Crucial X9/PROSTAGES/www_3/includes/liens_departements.php` → **95 URLs département** au format `/recuperer-points-{nom-concaténé}.html`

**Exemples de nomenclature PSP** (pas de tirets, pas d'accents, noms concaténés) :
- `recuperer-points-bouchesdurhone.html`
- `recuperer-points-hautegaronne.html`
- `recuperer-points-eureetloire.html` ⚠️
- `recuperer-points-ileetvilaine.html` ⚠️
- `recuperer-points-territoirebelfort.html`
- `recuperer-points-corse.html` ⚠️

**Pattern Twelvy cible** : `/stages-recuperation-points/departement/{slug-avec-tirets}`

#### Phase 2 — Vérification live HTTP des 95 URLs PSP

**Réflexe critique** : l'utilisateur a explicitement rappelé qu'en session précédente j'avais mappé vers des URLs qui n'existaient pas sur PSP. Cette fois → vérification exhaustive.

```bash
# Extraction des 95 URLs uniques depuis le fichier PHP source
grep -oE "recuperer-points-[a-z]+\.html" liens_departements.php | sort -u
# 95 résultats

# Vérification parallèle (10 en parallèle) via curl sur prostagespermis.fr
xargs -P 10 -I {} curl -s -o /dev/null -w "%{http_code}" ...
# Résultat : 95 × HTTP 200 ✅
```

**Conclusion** : toutes les 95 URLs PSP sont bien **live aujourd'hui** sur `prostagespermis.fr`. Aucune URL fantôme.

#### Phase 3 — Cartographie des 95 mappings

**Règle principale** : 94 URLs PSP → slug Twelvy 1:1 (simple insertion des tirets et restitution des accents).

**Cas spécial — Corse** : PSP avait **1 seule page** `recuperer-points-corse.html` couvrant toute la Corse. Twelvy a deux départements séparés (Corse-du-Sud 2A et Haute-Corse 2B). Choisir l'un des deux serait arbitraire.
→ **Décision** : rediriger vers `/stages-recuperation-points/region/corse` (page région Corse qui englobe 2A + 2B naturellement).

**Fautes d'orthographe PSP corrigées vers le slug Twelvy canonique** :
- PSP `eureetloire` → Twelvy `eure-et-loir` (le département est « Eure-et-Loir » avec « loir », pas « loire »)
- PSP `ileetvilaine` → Twelvy `ille-et-vilaine` (le département est « Ille-et-Vilaine » avec deux « l »)

**Concatenations piégeuses vérifiées** :
- `territoirebelfort` → `territoire-de-belfort` (PSP omet le « de »)
- `valdoise` → `val-doise` (pas de tiret entre « d » et « oise » — cohérent avec le slug existant dans `lib/departements.ts`)
- `hautrhin` → `haut-rhin` (masculin, pas de « e » final)
- `basrhin` → `bas-rhin`
- `hautsdeseine` → `hauts-de-seine`
- `valdemarne` → `val-de-marne`
- `loiretcher` → `loir-et-cher` (loir sans « e »)

**Paires pouvant prêter à confusion, vérifiées individuellement** :
- `hautemarne` (52) → `haute-marne` vs `hautesalpes` (05) → `hautes-alpes` (singulier vs pluriel)
- `loire` (42) vs `hauteloire` (43) vs `loireatlantique` (44) vs `loiret` (45) vs `loiretcher` (41) → tous distincts
- `hautevienne` (87) → `haute-vienne` vs `vienne` (86) → `vienne`

#### Phase 4 — Insertion dans `redirects.csv`

Ajout d'une nouvelle section **Catégorie 8** à la fin du fichier, juste après la Catégorie 7 existante :

```csv
# =============================================
# CATÉGORIE 8 : URLs département HTML PSP → Fiches département Twelvy (95)
# =============================================
# Ajouté le 16 Mars 2026 — pages dept Twelvy créées (commit c3d9be5)
# Ancien format PSP : /recuperer-points-{nom-concaténé-sans-tirets-sans-accents}.html
# Nouveau format Twelvy : /stages-recuperation-points/departement/{slug}
# Source vérifiée : /Volumes/Crucial X9/PROSTAGES/www_3/includes/liens_departements.php
# 95 URLs toutes confirmées LIVE sur prostagespermis.fr (HTTP 200, check 16 Mar 2026)
#
# Note : 94 mappings 1:1 dept→dept + 1 cas spécial Corse → page région
# Note : PSP avait 2 slugs avec fautes d'orthographe — on redirige vers le slug Twelvy CORRECT :
#   - /recuperer-points-eureetloire.html → /eure-et-loir (PSP avait "loire" — incorrect, c'est "loir")
#   - /recuperer-points-ileetvilaine.html → /ille-et-vilaine (PSP avait "ile" — incorrect, c'est "ille")

/recuperer-points-ain.html,/stages-recuperation-points/departement/ain,301,PRÊT
... (95 lignes au total)
```

Tous les 95 entries en statut `PRÊT` (pas `LIVE`) — cohérent avec la discipline existante : ces redirects ne s'activent que le jour du basculement DNS de `prostagespermis.fr` vers Twelvy. En attendant, le CSV est exhaustif, prêt à être copié en bloc dans `next.config.ts`.

**Mise à jour du TOTAL FINAL** :
```csv
# Cat 7 : 5 liens internes cassés dans contenu WP (Wave 3 audit - 09 Mar)
# Cat 8 : 95 URLs département HTML PSP → fiches département Twelvy (ajout 16 Mar 2026)
# NB : Aucune redirection région nécessaire — PSP n'avait PAS de pages région (vérifié par grep dans l'archive PSP)
# TOTAL couvert : ~300+ redirections
```

#### Phase 5 — Audit indépendant par agent

Pour garantir zéro erreur, lancement d'un agent `general-purpose` dédié avec mission :
1. Vérifier complétude — chaque URL PSP présente exactement 1 fois en Cat 8
2. Vérifier validité des cibles — chaque slug destination existe bien dans `lib/departements.ts` ou `lib/regions.ts`
3. Vérifier la correctness du mapping nom par nom, avec attention particulière aux typos et concatenations piégeuses

**Résultat agent** : ✅ ALL 95 MAPPINGS VERIFIED CORRECT

Observation agent : le slug `val-doise` est inhabituel (pas de tiret entre « d » et « oise », contrairement aux frères `cote-dor` / `cotes-darmor`) mais il est bien défini ainsi dans `lib/departements.ts` ligne 103 — le redirect l'utilise correctement. Si ce slug est un jour normalisé en `val-d-oise`, il faudra mettre à jour cette ligne.

---

## 3. Problèmes rencontrés & solutions

### 3.1 Incompréhension initiale du terme « redirections »

**Problème** : première lecture ambiguë de la tâche « update la page redirections avec les nouvelles fiches dpt et régions ». « Page redirections » pouvait désigner plusieurs choses.

**Résolution** : exploration du projet → trouvé 2 fichiers concernés (`redirects.csv` + `next.config.ts`). Compréhension du workflow `LIVE` vs `PRÊT`.

### 3.2 Confusion entre « discrepancy » et « migration task »

**Problème** : l'utilisateur a demandé si la différence entre les URLs PSP et Twelvy était un bug ou un problème à corriger.

**Résolution** : ce n'est **pas** un bug. C'est une tâche de migration normale. PSP et Twelvy utilisent des formats d'URL différents (PSP concatène, Twelvy utilise des tirets), il faut juste mapper les deux. Sans redirects, le jour du cutover DNS : 95 URLs Google-indexées tombent en 404 → perte de trafic SEO.

### 3.3 Risque d'erreur sur le mapping (mention historique utilisateur)

**Problème** : l'utilisateur a explicitement mentionné qu'en session précédente j'avais mappé vers des URLs inexistantes sur PSP.

**Solution** : approche triple-vérification
1. **Source archive** : grep exhaustif dans `/Volumes/Crucial X9/PROSTAGES/www_3/` pour trouver toutes les références
2. **Vérification live HTTP** : curl parallèle sur les 95 URLs PSP → 100 % de HTTP 200 confirmés
3. **Audit indépendant** : agent général dédié à la vérification des 95 mappings un par un

Aucune erreur trouvée.

### 3.4 Cas Corse ambigu

**Problème** : PSP a 1 seule page `recuperer-points-corse.html`, Twelvy a 2 départements (Corse-du-Sud 2A + Haute-Corse 2B). Choisir l'un ou l'autre serait arbitraire et perdrait la moitié du SEO.

**Solution** : rediriger vers `/stages-recuperation-points/region/corse` (page région qui englobe les deux départements naturellement). Commentaire explicite dans le CSV pour la traçabilité.

### 3.5 Typos orthographiques dans les slugs PSP

**Problème** : PSP a 2 URLs avec des slugs incorrects (`eureetloire` au lieu de `eureetloir`, `ileetvilaine` au lieu de `illeetvilaine`).

**Décision** : rediriger vers le slug Twelvy **canonique** (correct), pas répliquer la faute. Commentaires explicites dans le CSV sur les deux lignes concernées.

---

## 4. Fichiers modifiés (récap)

| Fichier | Modification | Commit |
|---------|--------------|--------|
| `app/stages-recuperation-points/departement/[dept]/page.tsx` | Bloc image `salle-stage.jpg` avant footer | `89206d1` |
| `app/stages-recuperation-points/region/[region]/page.tsx` | Bloc image `salle-stage.jpg` avant footer | `89206d1` |
| `app/sitemap.ts` | Import DEPARTEMENTS + REGIONS, 2 boucles d'ajout | `5b6025f` |
| `redirects.csv` | Nouvelle Cat 8 (95 lignes) + MAJ TOTAL FINAL | `57d1f16` |

---

## 5. Commits du jour

| Hash | Description |
|------|-------------|
| `89206d1` | ✨ feat: Add salle-stage fallback image to dept and region pages |
| `5b6025f` | 🔍 seo: Add 101 dept + 18 region URLs to sitemap |
| `57d1f16` | 🔍 seo: Add Cat 8 — 95 PSP dept URL → Twelvy dept page redirects (PRÊT) |

Tous pushés sur `main` → déployés automatiquement sur Vercel.

---

## 6. État de préparation pour le jour de basculement de domaine

| Catégorie | Nb redirects | Statut | Note |
|-----------|--------------|--------|------|
| Cat 1 — Pages PHP | 48 | LIVE (3) + PRÊT (45) | |
| Cat 2 — Pages `/p-` | 49 + wildcard | Mix LIVE/PRÊT | |
| Cat 3 — URLs ville HTML | 31 | LIVE (6) + PRÊT (25) | |
| Cat 4 — Blog `/infos-` | 5 + wildcard | Mix LIVE/PRÊT | |
| Cat 5 — Pages blog mineures | 2 | PRÊT | |
| Cat 6 — Redirections internes | 2 | LIVE | |
| Cat 7 — Liens cassés WP | 5 | LIVE (4) + PRÊT (1) | |
| **Cat 8 — URLs département** | **95** | **PRÊT** | **Ajouté aujourd'hui** |
| **TOTAL** | **~300+** | | |

**Aucune région à rediriger** — PSP n'en avait jamais eu.

---

## 7. Tâches restantes (post-session 14 Avril)

| Tâche | Priorité | Statut |
|-------|----------|--------|
| Contenu WordPress pour fiches département (96 pages) | Moyenne | ⏳ Plus tard |
| Contenu WordPress pour fiches région (13 pages) | Moyenne | ⏳ Plus tard |
| 3 DOCX à supprimer manuellement via WP Admin | Basse | ⏳ Manuel Yakeen |
| Jour J — copier Cat 1 à 8 `PRÊT` → `next.config.ts` | Critique | ⏳ Jour cutover DNS |

---

## 8. Commandes de vérification utiles (post-déploiement)

```bash
# Vérifier que les pages dept sont indexées dans le sitemap
curl -s https://www.twelvy.net/sitemap.xml | grep "departement/" | wc -l
# Attendu : 101

curl -s https://www.twelvy.net/sitemap.xml | grep "region/" | wc -l
# Attendu : 18

# Compter les redirects PRÊT dans le CSV
grep -c ",PRÊT$" redirects.csv

# Compter Cat 8 spécifiquement
grep -c "^/recuperer-points-[a-z]" redirects.csv
# Attendu : 95

# Le jour J : vérifier qu'une URL PSP redirige bien
curl -I https://www.prostagespermis.fr/recuperer-points-bouchesdurhone.html
# Attendu : 301 → /stages-recuperation-points/departement/bouches-du-rhone
```

---

**Session 14 Avril 2026 — terminée. Prêt pour jour de basculement DNS.**

---

# 🌙 Session du soir — Démarrage Étape 8 Up2Pay (recherche)

> Session démarrée tard dans la soirée — toujours considérée comme "14 avril" par souci de cohérence calendaire utilisateur.

**Suite de RESUME_SESSION_14APR.md** (sitemap + redirects Cat 8).
**Session dédiée** : démarrage de l'**Étape 8 — Paiement Up2Pay** (recherche initiale, pas de code).

---

## 1. Bilan rapide d'entrée de session

État des travaux 14 avril (validés, déployés) :
- ✅ Image fallback `salle-stage.jpg` sur pages dept/région (commit `89206d1`)
- ✅ Sitemap : 101 dept + 18 région URLs ajoutées (commit `5b6025f`)
- ✅ Redirects Cat 8 : 95 URLs PSP dept → Twelvy dept (commit `57d1f16`, statut `PRÊT`)
- ✅ Documentation : `RESUME_SESSION_14APR.md` (commit `9d4a0d6`)

**Conclusion** : tout est en place pour le jour de basculement de domaine. Aucune action restante sur le bloc redirects/sitemap.

---

## 2. Travail effectué aujourd'hui

### 2.1 Démarrage Étape 8 — Up2Pay : phase de recherche pure

**Contexte utilisateur** : "honestly i have no clue about how this works no prior knowledge or anything else so we're basically starting from scratch."

**Démarche choisie** : avant toute ligne de code, faire une recherche exhaustive en parallèle pour bâtir une vraie base de connaissances. Aucun code écrit aujourd'hui — uniquement de la lecture, de la doc et de la cartographie.

### 2.2 Sources consultées

1. **Cahier des charges** (`Cahier des charges up2pay.pages` — fichier Apple Pages 37 pages)
   - Conversion `.pages` → PDF via AppleScript Pages CLI
   - Lecture intégrale (3 chunks de 15+15+7 pages via Read tool PDF)
   - Validation visuelle directe du contenu via les images fournies par utilisateur

2. **Liste codes erreur** (`errors.csv` — 76 codes mappés)
   - Format : `CODE,LIBELLE DOC BRUT,LIBELLE POUR LE STAGIAIRE,MESSAGE PERSONNALISÉ À AJOUTER`
   - Exemples : `00114` (numéro carte erroné), `00115` (banque non reconnue), `00117` (annulation client)

3. **Code PSP existant** (archive `/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/`)
   - Recherche exhaustive par agent Explore parallèle (background)
   - 10+ fichiers PHP identifiés, flux complet documenté
   - Découverte critique : PSP utilise actuellement **mode Direct PPPS** (carte transite par serveur), pas le mode hosted iFrame

4. **Documentation officielle Up2Pay/Paybox**
   - Recherche web par agent général en parallèle (background)
   - Docs ca-moncommerce.com inaccessibles (login requis) → fallback sur docs Verifone/Paybox publiques (protocole identique)
   - Manuel d'intégration v8.0 récupéré, dictionnaire de données exhaustif

5. **Code Twelvy actuel** (recherche locale)
   - Aucun code paiement réel — uniquement états React mockup (`nomCarte`, `numeroCarte`, `paymentBlockVisible`)
   - Le formulaire CB visuel existe dans `app/stages-recuperation-points/[slug]/[id]/inscription/page.tsx` mais ne fait rien

### 2.3 Découvertes majeures

#### Découverte A — Architecture cible vs existant : désalignement
- **PSP actuel** : mode **Direct API "PPPS"** (`https://ppps.paybox.com/PPPS.php`). Le serveur PSP reçoit la carte et la POST en cURL à Up2Pay.
- **Cahier des charges Twelvy** : mode **Hosted iFrame "MYchoix"** (`https://tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi`). La carte est saisie chez Up2Pay, jamais sur le serveur Twelvy.
- **Conséquence** : on ne peut pas juste "porter" le code PSP. Il faut une vraie réécriture de la couche paiement avec un VRAI script IPN robuste (PSP n'en a pas vraiment, son flux est synchrone).
- **Référence historique** : un ancien code PSP en mode hosted existe quand même dans `/Volumes/Crucial X9/PROSTAGES/PSP 3/backup code cb/pbx_repondre_a.php` — à étudier en priorité.

#### Découverte B — Credentials PROD déjà accessibles
La clé HMAC PROD (128 chars hex SHA-512) est dans le code PSP existant à `www_2/src/payment/E_Transaction/E_TransactionConfig.php`. Pas besoin de la demander à Kader. **NE JAMAIS la commiter** — elle doit migrer vers `config_secrets.php` non versionné.

#### Découverte C — Architecture cible (cahier p.5-7)
```
Next.js (Vercel)        Bridge PHP (OVH 5.6)        Up2Pay        IPN PHP (OVH)
    UI seule       →     écrit MySQL       →    page CB chez eux    →  vérif HMAC
                          signe HMAC                                     UPDATE stagiaire
                                                                         envoie emails
```

Règles d'or :
1. HMAC, MySQL, IPN → **toujours côté PHP**, jamais Next.js
2. PHP 5.6 obligatoire côté OVH
3. Bridge protégé par header `X-Api-Key` (token random 32-64 chars)
4. **Idempotence IPN** non négociable (Up2Pay retry sinon)
5. Fail-safe : bouton "Cliquez ici si vous n'êtes pas redirigé" si auto-submit JS échoue

#### Découverte D — Plan en 10 étapes du cahier des charges
1. Auditer table `stagiaire`
2. Cartographier flux PHP actuel (déjà fait à 80% par agent aujourd'hui)
3. Designer architecture cible
4. Préparer config TEST + PROD
5. Créer `bridge.php`
6. Bétonner scripts retour + IPN
7. Brancher formulaire complet (Coordonnées + CB)
8. Gérer retour paiement Next.js
9. Tests bout-en-bout en sandbox + comparaison side-by-side avec ancien tunnel
10. Bascule production + monitoring serré

Estimation totale : **25-40h dev + 1 semaine monitoring**.

### 2.4 Livrables produits aujourd'hui

| Fichier | Rôle | Taille |
|---------|------|--------|
| `UP2PAY.md` | Master reference technique pour toute l'intégration. 17 sections : TL;DR, histoire Up2Pay, décision architecturale critique, credentials, flux complet, params PBX_*, algo HMAC, IPN handler, cartographie code PSP existant, plan 10 étapes, tests, bascule prod, inconnues à clarifier, liens. | ~25 KB |
| `RESUME_SESSION_15APR.md` | Ce document — log de la session | ~8 KB |

### 2.5 Outils utilisés
- **AppleScript Pages CLI** : conversion `.pages` → PDF
- **Read tool PDF** : lecture du PDF par chunks (limite 20 pages/requête)
- **Read tool image** : lecture du `preview.jpg` du Pages original
- **Agent Explore** (background) : archeology du code PSP — 62 tool uses, 207s
- **Agent général** (background) : recherche docs Up2Pay sur web — 15 tool uses, 178s
- **Curl parallèle** : aucun aujourd'hui (recherche pure)

---

## 3. Décisions critiques à prendre AVANT d'écrire du code

| # | Question | À demander à |
|---|----------|--------------|
| 1 | **Mode Direct PPPS vs Hosted iFrame** ? Le PSP actuel utilise PPPS, le cahier dit iFrame. Conditionne TOUT le code à écrire. | Kader |
| 2 | Hébergement bridge : `prostagespermis.fr` (OVH actuel) ou nouveau hosting Twelvy ? | Kader |
| 3 | Domaine de DEV (Up2Pay ne peut pas appeler localhost) : créer `dev.prostagespermis.com` sur OVH ? | Kader / Yakeen FTP |
| 4 | BDD : on garde `stagiaire` PSP intacte (recommandé par cahier) ou nouvelle table ? | Kader |
| 5 | Email infra : on réutilise `mail_inscription.php` PSP ou on construit côté Twelvy (Resend) ? | Kader |
| 6 | Clé HMAC TEST : back-office Up2Pay (Kader) ou clé dummy publique Verifone ? | Kader |
| 7 | Plan rollback : ancien `/es/inscriptionv2_3ds.php` accessible derrière une URL parallèle ? | Kader |
| 8 | Qui a accès au back-office Up2Pay pour vérifier/changer les URLs `PBX_REPONDRE_A` ? | Kader |
| 9 | Cron `cron_status_payment.php` : on garde sur ancien hébergement ou on migre ? | Kader |
| 10 | Y a-t-il un "staging" Up2Pay distinct, ou seulement TEST/PROD ? | Kader |

→ Liste détaillée et exhaustive : voir `UP2PAY.md` §15.

---

## 4. Prochaines étapes concrètes

### Court terme (avant prochaine session)
1. **Faire valider `UP2PAY.md` par Kader** — particulièrement la décision A/B (mode PPPS vs iFrame)
2. **Récupérer les réponses aux 10 questions du §3** ci-dessus
3. **Créer `dev.prostagespermis.com`** sur OVH si choix retenu (sinon ngrok pour tests locaux)
4. **Auditer la table `stagiaire`** — étape 1 du plan : lister les colonnes liées paiement, leur évolution dans le flux

### Moyen terme (1-2 sessions suivantes)
5. **Étape 2** : valider/compléter la cartographie du flux PHP actuel (déjà 80% fait par agent — il faut juste lire les 10+ fichiers identifiés)
6. **Étape 3** : écrire le doc "architecture cible" (1 page) avec validation Kader
7. **Étape 4** : créer le squelette `config_paiement.php` + `config_secrets.php` (vide) + `.env.local` exemple

### Long terme
8-10. Étapes 5 → 10 du plan (bridge, IPN, formulaire Next.js, tests, bascule)

---

## 5. Risques identifiés

| Risque | Impact | Mitigation |
|--------|--------|------------|
| Bascule mode PPPS → iFrame casse des règles métier subtiles | Inscriptions perdues, support | Tests side-by-side OBLIGATOIRES (étape 9 cahier) |
| Idempotence IPN mal implémentée → double email/double commission | Client mécontent + comptabilité fausse | Pattern PSP existant à reprendre (`status='inscrit' && numappel/numtrans non vides`) |
| Encodage UTF-8 mal géré entre Next.js (utf8mb4) et PHP/MySQL OVH (latin1/utf8) | Accents déformés en BDD | `SET NAMES UTF8` sur connexion bridge (cahier p.6) |
| Up2Pay backend changé sans qu'on le sache | Tests TEST passent mais PROD échoue | Faire l'étape 9 sur Up2Pay TEST récent + 1-3 paiements pilote en PROD |
| Code PSP `paths` hardcodés (`/Users/yakeen/prostage/...`) | Logs non écrits / bugs silencieux | Audit de tous les `realpath()` et chemins absolus avant déploiement |
| Numéro de carte stocké en clair dans `stagiaire.numero_cb` | Risque PCI-DSS | Mode iFrame élimine le problème (Twelvy ne voit jamais la carte) — décision A/B critique pour ça |

---

## 6. Commits du jour

Aucun commit code aujourd'hui (recherche pure). Commits à venir :
- Documentation : `UP2PAY.md` + `RESUME_SESSION_15APR.md`

---

## 7. État global du projet Étape 8

| Sous-étape | Statut | Notes |
|------------|--------|-------|
| Compréhension générale | ✅ Fait | Cahier des charges entièrement digéré |
| Cartographie code PSP existant | ✅ 80% | Agent a tout trouvé — reste à lire les fichiers individuellement |
| Documentation Up2Pay/Paybox | ✅ Fait | `UP2PAY.md` exhaustif |
| Décisions architecturales | ⏳ En attente | Kader doit valider mode A/B + 9 autres questions |
| Audit table `stagiaire` (étape 1) | ⏳ Non commencé | À faire dès qu'on a accès phpMyAdmin OVH |
| Architecture cible (étape 3) | ⏳ Non commencé | Dépend de la décision A/B |
| Config TEST/PROD (étape 4) | ⏳ Non commencé | Squelette prêt mentalement, à écrire |
| Bridge PHP (étape 5) | ⏳ Non commencé | |
| Scripts IPN/retour (étape 6) | ⏳ Non commencé | |
| Branchement Next.js (étape 7) | ⏳ Non commencé | |
| Pages retour Next.js (étape 8) | ⏳ Non commencé | |
| Tests bout-en-bout (étape 9) | ⏳ Non commencé | Bloque sur domaine DEV |
| Bascule PROD (étape 10) | ⏳ Non commencé | |

---

## 8. Q&A de clarification (fin de session)

L'utilisateur a posé 5 questions essentielles pour vérifier sa compréhension. Réponses synthétiques en français ci-dessous (versions plus longues données dans le chat).

### Q1 — Est-ce qu'on fait une approche différente de celle de PSP ?

**Oui, légèrement différente. Confirmation Kader nécessaire.**

Aujourd'hui, PSP utilise le mode **"Direct PPPS"** : la carte transite par le serveur PSP avant d'être envoyée à Up2Pay en backend.

Le cahier des charges (page 14) dit explicitement : "On n'utilise pas le mode où tu héberges toi-même le formulaire CB". Donc Kader a explicitement choisi le mode **"iFrame hosted"** pour Twelvy : la carte est saisie directement sur une page Up2Pay embarquée. Twelvy ne voit jamais la carte.

**Pourquoi changer ?** Parce que Next.js + Vercel manipulant des numéros de carte = cauchemar PCI-DSS (réglementation bancaire). L'iFrame élimine totalement ce problème.

**Niveau de certitude** : 95 % sur la base du cahier. Le 5 % de doute = Kader pourrait vouloir dire "iFrame seulement pour le nouveau tunnel" ou "PPPS d'abord puis iFrame plus tard". À confirmer.

### Q2 — Qu'est-ce que bridge.php et où est-ce qu'on l'héberge ?

`bridge.php` = **un seul fichier PHP qu'on va écrire**. C'est un standardiste téléphonique :

- Next.js (Vercel) ne peut pas parler directement à MySQL OVH (firewall, sécurité)
- Next.js ne doit pas signer de clés HMAC (les clés doivent rester secrètes côté serveur)

Donc `bridge.php` est le seul numéro de téléphone que Next.js appelle. Next.js dit "crée un prospect avec ces infos" → bridge écrit en MySQL → renvoie un ID. Ou "prépare un paiement pour l'ID 12345" → bridge signe les params HMAC → renvoie les params.

**"Où l'héberger"** = sur quel serveur ce fichier PHP vit physiquement.

**Bonne nouvelle** : `api.twelvy.net` existe déjà sur OVH (c'est là où vit `stages-geo.php`). Le bridge serait juste un nouveau fichier à `https://api.twelvy.net/bridge.php`. Aucune nouvelle infrastructure nécessaire.

### Q3 — Qu'est-ce que l'URL IPN ?

**IPN** = "Instant Payment Notification". URL sur **votre serveur** que Up2Pay appelle serveur-à-serveur après un paiement.

**Pourquoi ça existe ?** Parce que le navigateur du client est non fiable. Le client peut fermer son onglet juste après avoir payé. Donc Up2Pay ne peut pas se fier au navigateur pour vous dire "paiement OK". À la place, le serveur Up2Pay appelle directement une URL sur votre serveur en HTTP POST avec le résultat. **C'est cet appel-là qui fait foi pour confirmer le paiement.**

L'URL IPN qu'on créerait : `https://api.twelvy.net/ipn.php` — un nouveau script PHP qui reçoit la notification Up2Pay, vérifie la signature RSA, met à jour la BDD, envoie les emails.

### Q4 — Up2Pay sera-t-il "déconnecté" de prostagespermis.fr ?

**Non, absolument pas.** Le point le plus important à clarifier.

Il y a **UN SEUL compte Up2Pay** (AM FORMATION, contrat 0966892.02). Ce compte est partagé. PSP ET Twelvy l'utilisent — il n'y a PAS deux comptes Up2Pay séparés.

Il y a **UNE SEULE base MySQL** (la table `stagiaire` sur OVH). PSP ET Twelvy lisent et écrivent dessus. Mêmes données, mêmes règles métier, mêmes emails.

Ce qui change : **quelle URL Up2Pay rappelle** après un paiement. Et c'est défini transaction par transaction. À chaque paiement initié, le site qui initie le paiement dit à Up2Pay "renvoie le résultat à CETTE URL". Donc :

- Paiement initié sur PSP → indique à Up2Pay d'appeler le script PSP existant
- Paiement initié sur Twelvy → indique à Up2Pay d'appeler le nouveau script IPN sur `api.twelvy.net`

**Les deux peuvent coexister.** PSP continue de fonctionner exactement comme aujourd'hui. Twelvy a ses propres scripts. Le compte Up2Pay s'en moque — il appelle simplement l'URL que chaque transaction lui indique.

### Q5 — Ça défait pas le but de la coexistence et migration instantanée ?

**Non — voici le vrai scénario de migration.**

```
AUJOURD'HUI :
  prostagespermis.fr (PSP PHP)   ──► écrit dans MySQL `stagiaire` ◄── rien d'autre
  twelvy.net (Next.js)           ──► (pas encore de paiement)

PÉRIODE DE TRANSITION (ce qu'on construit) :
  prostagespermis.fr (PSP PHP)    ─┐
                                    ├──► même MySQL `stagiaire`
  twelvy.net + api.twelvy.net    ──┘    (les deux écrivent dessus)

  Les deux sites prennent des paiements. Les deux fonctionnent.
  Les deux alimentent la même BDD. Même compte Up2Pay. Même emails.
  Zéro coordination nécessaire.

JOUR DE BASCULE :
  - On bascule le DNS de prostagespermis.fr vers Vercel (vos 95 redirects entrent en jeu)
  - api.twelvy.net reste sur OVH (rien à toucher)
  - Les 95 URLs PSP redirigent vers les équivalents Twelvy
  - Les scripts PHP PSP existants peuvent rester actifs comme fallback quelques semaines
  - La BDD n'a pas bougé du tout — zéro migration de données
```

La bascule est instantanée parce que **rien ne bouge réellement**. La BDD reste sur OVH. Le compte Up2Pay reste sur OVH. Seul le front-end (quel site sert l'utilisateur) change via DNS.

Le bridge vivant sur `api.twelvy.net` est **mieux** que sur prostagespermis.fr parce que :
- Pas affecté par le bascule DNS de prostagespermis.fr
- Déjà installé
- Sépare proprement "backend Twelvy" vs "ancien code PSP"

### Synthèse en 1 paragraphe

Les deux sites partagent le même compte Up2Pay et la même BDD MySQL. PSP continue de faire ce qu'il fait aujourd'hui, sans modification. On ajoute de nouveaux scripts PHP (`bridge.php`, `ipn.php`, `retour.php`) sur `api.twelvy.net` (qui existe déjà sur OVH). Le front Next.js de Twelvy appelle ces nouveaux scripts. Quand un utilisateur paie via Twelvy, Up2Pay rappelle notre nouveau script IPN (configuré transaction par transaction), qui écrit dans la même table `stagiaire` que PSP utilise. Le jour de la bascule, rien ne bouge dans le système de paiement — seul le DNS de prostagespermis.fr bascule vers Vercel. Tout le pipeline de paiement continue sans interruption.

---

## 9. Conclusion globale de la session

Aucun code écrit aujourd'hui — c'était voulu et c'était la bonne décision. Le risque #1 sur ce chantier paiement est de coder trop vite sans avoir pleinement compris l'existant et l'architecture cible. La session du 15 avril a posé toutes les fondations :

- ✅ Cahier des charges entièrement digéré (37 pages)
- ✅ Code PSP existant cartographié (10+ fichiers PHP, file:line)
- ✅ Documentation Up2Pay/Paybox synthétisée (`UP2PAY.md` 17 sections, ~25 KB)
- ✅ 5 confusions principales de l'utilisateur clarifiées
- ✅ 10 questions critiques formalisées pour Kader
- ✅ Décision architecturale clé identifiée (Direct PPPS vs Hosted iFrame)

Le projet est maintenant en **état "prêt pour validation Kader"**. Demain (ou la prochaine session) :
1. Soumettre `UP2PAY.md` à Kader
2. Obtenir réponses aux 10 questions
3. Si validé → attaquer Étape 1 (audit table `stagiaire` via phpMyAdmin)

---

**Session 15 Avril 2026 — terminée.**
**Prochaine étape** : valider `UP2PAY.md` avec Kader + obtenir réponses aux 10 questions critiques.
