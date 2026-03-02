// TWELVY - Professional driving license points recovery platform
import type { Metadata } from "next";
import { Geist, Geist_Mono, Poppins } from "next/font/google";
import "./globals.css";
import Header from "@/components/layout/Header";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

const poppins = Poppins({
  variable: "--font-poppins",
  subsets: ["latin"],
  weight: ["400", "500", "600", "700"],
  display: 'swap',
});

export const metadata: Metadata = {
  title: {
    default: "Stage récupération de points pas cher - Twelvy",
    template: "%s - Twelvy",
  },
  description: "Trouvez un stage de récupération de points pas cher partout en France. Réservez votre stage de sensibilisation à la sécurité routière au meilleur prix.",
  metadataBase: new URL('https://www.twelvy.net'),
  alternates: {
    canonical: '/',
  },
  openGraph: {
    type: 'website',
    locale: 'fr_FR',
    url: 'https://www.twelvy.net',
    siteName: 'Twelvy',
    title: 'Stage récupération de points pas cher - Twelvy',
    description: 'Trouvez un stage de récupération de points pas cher partout en France. Réservez votre stage de sensibilisation à la sécurité routière au meilleur prix.',
  },
  twitter: {
    card: 'summary_large_image',
  },
  robots: {
    index: true,
    follow: true,
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="fr">
      <body
        className={`${geistSans.variable} ${geistMono.variable} ${poppins.variable} antialiased`}
      >
        <Header />
        {children}
      </body>
    </html>
  );
}
