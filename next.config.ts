import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  async redirects() {
    return [
      // Old PSP PHP URLs still referenced in migrated blog post content
      {
        source: '/agrements.php',
        destination: '/agrements-du-stage',
        permanent: true,
      },
      {
        source: '/annulation-permis.php',
        destination: '/annulation-permis',
        permanent: true,
      },
      {
        source: '/bareme-retrait-points.php',
        destination: '/bareme-de-retrait-de-points',
        permanent: true,
      },
      // Old PSP city URLs found in blog post content (crawl P3 - 07 Mar)
      {
        source: '/recuperation-points-LYON-7EME-69007-69.html',
        destination: '/stages-recuperation-points/lyon',
        permanent: true,
      },
      {
        source: '/recuperation-points-TOULON-83000-83.html',
        destination: '/stages-recuperation-points/toulon',
        permanent: true,
      },
      {
        source: '/recuperation-points-NICE-06000-06.html',
        destination: '/stages-recuperation-points/nice',
        permanent: true,
      },
      // Placeholder pages that are anchor sections within other articles
      // (PSP nav items that link to #sections, not standalone pages)
      {
        source: '/comment-obtenir-mes-acces-telepoints',
        destination: '/consulter-ses-points',
        permanent: true,
      },
      {
        source: '/obtenir-mes-acces-telepoints',
        destination: '/consulter-ses-points',
        permanent: true,
      },
      {
        source: '/comment-contester-un-retrait',
        destination: '/suspension-de-permis-et-retrait-de-permis',
        permanent: true,
      },
      {
        source: '/recuperer-des-points-sur-mon-permis-probatoire',
        destination: '/recuperer-ses-points',
        permanent: true,
      },
      {
        source: '/combien-de-temps-pour-recuperer-ses-points',
        destination: '/stage-de-sensibilisation-a-la-securite-routiere',
        permanent: true,
      },
      {
        source: '/dans-quels-cas-faire-un-stage',
        destination: '/retrait-de-permis',
        permanent: true,
      },
      {
        source: '/comment-contester-mon-amende',
        destination: '/payer-son-amende',
        permanent: true,
      },
      {
        source: '/comment-consulter-mon-solde-de-points',
        destination: '/consulter-ses-points',
        permanent: true,
      },
      // Footer link redirections
      {
        source: '/conditions-generales',
        destination: 'https://www.prostagespermis.fr/CGV_PROSTAGESPERMIS-STAGIAIRES.pdf',
        permanent: false,
      },
      {
        source: '/aide-et-contact',
        destination: 'https://www.khapeo.com/wp/psp/aide-et-contact-prostagespermis/',
        permanent: false,
      },
    ]
  },
};

export default nextConfig;
