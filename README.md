# TWELVY - Points Recovery Courses Platform

Professional driving license points recovery courses platform built with Next.js and OVH MySQL.

## Quick Start

### Prerequisites
- Node.js 18+ and npm
- Git

### Installation

```bash
git clone https://github.com/yakeeniacloud/TWELVY.git
cd TWELVY
npm install
```

### Environment Setup

Copy `.env.example` to `.env.local` and fill in your values:

```bash
cp .env.example .env.local
```

Edit `.env.local`:
```
OVH_API_URL=https://api.twelvy.net
OVH_API_KEY=your_api_key_here
```

### Development

```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000) to view the site.

### Testing

1. **POST Test**: Click "📤 Send Test Booking (POST)" button
   - Creates a test booking in OVH MySQL
   - Should return booking ID and reference

2. **GET Test**: Click "📥 Test GET Request" button
   - Verifies PHP 8.1 execution on OVH
   - Confirms API connectivity

### Building for Production

```bash
npm run build
npm start
```

## Documentation

See [CLAUDE.md](./CLAUDE.md) for complete project documentation including:
- Architecture overview
- OVH setup and configuration
- API documentation
- Deployment instructions
- Troubleshooting guide

## Project Structure

```
TWELVY/
├── app/
│   ├── api/                    # Next.js API routes
│   │   ├── test-booking/       # POST proxy to OVH
│   │   └── test-get/           # GET proxy to OVH
│   ├── layout.tsx              # Root layout
│   ├── page.tsx                # Home page with test buttons
│   └── globals.css             # Global styles
├── CLAUDE.md                   # Complete documentation
├── README.md                   # This file
└── ...config files
```

## Technologies

- **Frontend**: Next.js 16, React 19, TypeScript
- **Styling**: Tailwind CSS
- **Database**: MySQL on OVH
- **Backend API**: PHP 8.1 on OVH
- **Deployment**: Vercel

## Repository

- **GitHub**: https://github.com/yakeeniacloud/TWELVY
- **Main Branch**: main (auto-deploys to Vercel)

## Support

For detailed information about setup, configuration, and troubleshooting, see [CLAUDE.md](./CLAUDE.md).
