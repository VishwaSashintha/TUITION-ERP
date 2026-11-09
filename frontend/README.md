# TuitionPro ERP — Frontend Presentation Layer

The frontend of TuitionPro ERP is organized into a clean presentation layer utilizing a custom server-side view template engine, responsive CSS3 design tokens, vanilla JavaScript, and Chart.js visualizations.

## Directory Structure

```
frontend/
├── assets/
│   ├── css/
│   │   └── style.css       # Design tokens, Roboto typography rules, component classes
│   ├── js/
│   │   └── app.js          # Chart.js defaults, responsive sidebar, auto-dismissing alerts
│   └── images/
│       ├── logo.svg        # Standard brand SVG logo
│       ├── logo-white.svg  # Light brand SVG logo for dark backgrounds
│       └── logo-mark.svg   # Compact brand mark icon
├── languages/              # Localization files
├── views/                  # 67 UI views organized across 18 functional domains
│   ├── academic/           # Batches, subjects, learning materials, assignments
│   ├── attendance/         # Session roll-call sheets, live QR scanner, history
│   ├── auth/               # SaaS login portal and role demo triggers
│   ├── communication/      # Announcements and notifications
│   ├── errors/             # 403, 404, and 500 branded error pages
│   ├── examinations/       # Exam scheduling, marks entry, and merit rankings
│   ├── expenses/           # Expense tracking, double-entry trial balance, P&L
│   ├── fees/               # Invoices, partial payment collection, receipts
│   ├── institute/          # Executive, receptionist, and teacher dashboards
│   ├── layouts/            # Master layout templates (app.php, auth.php)
│   ├── platform/           # Super Admin administration and tenant provisioning
│   ├── portals/            # Dedicated student and parent portals
│   ├── reports/            # Operational, financial, and attendance analytics
│   ├── settings/           # Institute profiles, branches, users, and backup manager
│   ├── students/           # Directory, onboarding, and printable QR ID cards
│   ├── teachers/           # Faculty directory and compensation profiles
│   ├── teacher_payments/   # Payroll calculation and payout voucher generation
│   └── timetable/          # Weekly timetable grid and daily class sessions
└── package.json            # Frontend metadata and linting scripts
```

## Design System Specifications

- **Theme:** Modern Professional Light Theme (`#F7F8FA` page canvas, `#FFFFFF` cards, `#E5E7EB` borders).
- **Brand Palette:**
  - Charcoal Primary: `#0C0C0C` (Sidebar navigation and high-contrast headings)
  - Brand Green CTA: `#4BA95F` (Primary action buttons, active sidebar pills, focus rings)
  - Sky Blue Accent: `#77DDFA` (Attendance metrics and information pills)
  - Amber Orange: `#FEA339` (Due balances, pending tasks, and warnings)
  - Purple Accent: `#9985FB` (Coursework, batches, and academic indicators)
  - Coral Alert: `#FF908D` (Expense indicators and danger badges)
- **Universal Typography:** Google Fonts Roboto enforced globally across all UI elements, headings, body text, form controls, buttons, tables, and Chart.js instances.
- **Tabular Numerics:** All currency figures, invoice sums, and metrics enforce `font-variant-numeric: tabular-nums` for alignment.
