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
    ]
  },
};

export default nextConfig;
