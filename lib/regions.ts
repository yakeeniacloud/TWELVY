export interface Region {
  name: string
  slug: string
  depts: string[]
}

export const REGIONS: Region[] = [
  {
    name: 'Auvergne-Rhône-Alpes',
    slug: 'auvergne-rhone-alpes',
    depts: ['01', '03', '07', '15', '26', '38', '42', '43', '63', '69', '73', '74'],
  },
  {
    name: 'Bourgogne-Franche-Comté',
    slug: 'bourgogne-franche-comte',
    depts: ['21', '25', '39', '58', '70', '71', '89', '90'],
  },
  {
    name: 'Bretagne',
    slug: 'bretagne',
    depts: ['22', '29', '35', '56'],
  },
  {
    name: 'Centre-Val de Loire',
    slug: 'centre-val-de-loire',
    depts: ['18', '28', '36', '37', '41', '45'],
  },
  {
    name: 'Corse',
    slug: 'corse',
    depts: ['2A', '2B'],
  },
  {
    name: 'Grand Est',
    slug: 'grand-est',
    depts: ['08', '10', '51', '52', '54', '55', '57', '67', '68', '88'],
  },
  {
    name: 'Hauts-de-France',
    slug: 'hauts-de-france',
    depts: ['02', '59', '60', '62', '80'],
  },
  {
    name: 'Île-de-France',
    slug: 'ile-de-france',
    depts: ['75', '77', '78', '91', '92', '93', '94', '95'],
  },
  {
    name: 'Normandie',
    slug: 'normandie',
    depts: ['14', '27', '50', '61', '76'],
  },
  {
    name: 'Nouvelle-Aquitaine',
    slug: 'nouvelle-aquitaine',
    depts: ['16', '17', '19', '23', '24', '33', '40', '47', '64', '79', '86', '87'],
  },
  {
    name: 'Occitanie',
    slug: 'occitanie',
    depts: ['09', '11', '12', '30', '31', '32', '34', '46', '48', '65', '66', '81', '82'],
  },
  {
    name: 'Pays de la Loire',
    slug: 'pays-de-la-loire',
    depts: ['44', '49', '53', '72', '85'],
  },
  {
    name: 'Provence-Alpes-Côte d\'Azur',
    slug: 'provence-alpes-cote-dazur',
    depts: ['04', '05', '06', '13', '83', '84'],
  },
  {
    name: 'Guadeloupe',
    slug: 'guadeloupe',
    depts: ['971'],
  },
  {
    name: 'Martinique',
    slug: 'martinique',
    depts: ['972'],
  },
  {
    name: 'Guyane',
    slug: 'guyane',
    depts: ['973'],
  },
  {
    name: 'La Réunion',
    slug: 'la-reunion',
    depts: ['974'],
  },
  {
    name: 'Mayotte',
    slug: 'mayotte',
    depts: ['976'],
  },
]

export const getRegionBySlug = (slug: string): Region | undefined =>
  REGIONS.find(r => r.slug === slug)
