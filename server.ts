import express, { Request, Response, NextFunction } from 'express';
import session from 'express-session';
import cookieParser from 'cookie-parser';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT ? parseInt(process.env.PORT, 10) : 3000;
const APP_NAME = process.env.APP_NAME || 'Sanghavi Vyapari';

// Trust proxy for secure cookies in Cloud Run / reverse proxy environments
app.set('trust proxy', 1);

// Configure template engine & static assets
app.set('views', path.join(__dirname, 'views'));
app.set('view engine', 'ejs');
app.use(express.static(path.join(__dirname, 'public')));
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(cookieParser());

app.use(
  session({
    secret: process.env.SESSION_SECRET || 'vyapari-secret-session-key',
    resave: false,
    saveUninitialized: false,
    cookie: {
      maxAge: 30 * 24 * 60 * 60 * 1000,
      sameSite: 'none',
      secure: true,
      httpOnly: true,
    },
  })
);

// Declare custom session typing
declare module 'express-session' {
  interface SessionData {
    user?: { name: string; email: string };
    flash?: { success?: string; error?: string };
  }
}

// Helpers
function money(val: number | string | null | undefined): string {
  const num = typeof val === 'number' ? val : parseFloat(String(val || 0)) || 0;
  return num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function weight3(val: number | string | null | undefined): string {
  const num = typeof val === 'number' ? val : parseFloat(String(val || 0)) || 0;
  return num.toLocaleString('en-IN', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
}

// Flash & Global middleware with cookie fallback
app.use((req: Request, res: Response, next: NextFunction) => {
  // Check auth_user cookie fallback for iframe environments where session cookie may be restricted
  if (!req.session.user && req.cookies && req.cookies.auth_user) {
    try {
      const parsed = JSON.parse(Buffer.from(req.cookies.auth_user, 'base64').toString('utf-8'));
      if (parsed && parsed.email) {
        req.session.user = parsed;
      }
    } catch {
      // ignore parsing error
    }
  }

  // Check auth query param fallback
  if (!req.session.user && req.query && req.query.auth) {
    try {
      const parsed = JSON.parse(Buffer.from(String(req.query.auth), 'base64').toString('utf-8'));
      if (parsed && parsed.email) {
        req.session.user = parsed;
      }
    } catch {
      // ignore parsing error
    }
  }

  const flash = req.session.flash || {};
  req.session.flash = {};

  res.locals.appName = APP_NAME;
  res.locals.user = req.session.user || null;
  res.locals.flash = flash;
  res.locals.money = money;
  res.locals.weight3 = weight3;
  res.locals.today = new Date().toISOString().split('T')[0];

  next();
});

function requireLogin(req: Request, res: Response, next: NextFunction) {
  if (!req.session.user) {
    return res.redirect('/login');
  }
  next();
}

function setFlash(req: Request, key: 'success' | 'error', message: string) {
  if (!req.session.flash) {
    req.session.flash = {};
  }
  req.session.flash[key] = message;
}

// In-memory data store
interface Vyapari {
  id: number;
  vyapari_name: string;
  company_name: string;
  email_id: string;
  primary_number: string;
  secondary_number: string;
  gst_number: string;
  city: string;
  state: string;
  deleted_at: string | null;
  created_at: string;
  updated_at: string;
}

interface Product {
  id: number;
  vyapari_id: number | null;
  product_name: string;
  purity: number;
  rate: number;
  deleted_at: string | null;
  created_at: string;
  updated_at: string;
}

interface Transaction {
  id: number;
  vyapari_id: number;
  product_id: number | null;
  transaction_type: string;
  liya_diya: 'liya' | 'diya';
  product_name: string;
  gross_weight: number;
  net_weight: number;
  melting: number;
  rate: number;
  cash_charge_amount: number;
  bill_charge_amount: number;
  ten_gram_bill_995_rate: number;
  ten_gram_cash_995_rate: number;
  purity_999: number;
  purity_995: number;
  gst_bhav: number;
  cash_bhav: number;
  cash_amount: number;
  rtgs_amount: number;
  balance_cat_995: number;
  transaction_date: string;
  notes: string;
  deleted_at: string | null;
  created_at: string;
  updated_at: string;
}

let nextVyapariId = 4;
let nextProductId = 4;
let nextTransactionId = 4;

const vyaparis: Vyapari[] = [
  {
    id: 1,
    vyapari_name: 'Rajesh Bullion & Sons',
    company_name: 'Rajesh Jewellers Pvt Ltd',
    email_id: 'rajesh@rajeshbullion.example',
    primary_number: '9876543210',
    secondary_number: '',
    gst_number: '24AAAAA0000A1Z5',
    city: 'Ahmedabad',
    state: 'Gujarat',
    deleted_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
  {
    id: 2,
    vyapari_name: 'Suresh Gold Mart',
    company_name: 'Suresh & Co',
    email_id: 'suresh@goldmart.example',
    primary_number: '9820012345',
    secondary_number: '',
    gst_number: '27BBBBB1111B1Z2',
    city: 'Mumbai',
    state: 'Maharashtra',
    deleted_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
  {
    id: 3,
    vyapari_name: 'Mehta Brothers',
    company_name: 'Mehta Bullion Corp',
    email_id: 'contact@mehtabullion.example',
    primary_number: '9898011223',
    secondary_number: '',
    gst_number: '24CCCCC2222C1Z9',
    city: 'Surat',
    state: 'Gujarat',
    deleted_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
];

const products: Product[] = [
  {
    id: 1,
    vyapari_id: null,
    product_name: 'Gold Bar 995',
    purity: 99.5,
    rate: 0,
    deleted_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
  {
    id: 2,
    vyapari_id: 1,
    product_name: 'Kada 916',
    purity: 91.6,
    rate: 1.5,
    deleted_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
  {
    id: 3,
    vyapari_id: 2,
    product_name: 'Chain 916',
    purity: 91.6,
    rate: 2.0,
    deleted_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
];

let transactions: Transaction[] = [
  {
    id: 1,
    vyapari_id: 1,
    product_id: 2,
    transaction_type: 'maal-liya',
    liya_diya: 'liya',
    product_name: 'Kada 916',
    gross_weight: 50.0,
    net_weight: 50.0,
    melting: 91.6,
    rate: 1.5,
    cash_charge_amount: 0,
    bill_charge_amount: 0,
    ten_gram_bill_995_rate: 72000,
    ten_gram_cash_995_rate: 71000,
    purity_999: 46.55,
    purity_995: 46.784,
    gst_bhav: 7416,
    cash_bhav: 7100,
    cash_amount: 332166.4,
    rtgs_amount: 346950.14,
    balance_cat_995: 0,
    transaction_date: new Date().toISOString().split('T')[0],
    notes: 'Initial purchase lot',
    deleted_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
  {
    id: 2,
    vyapari_id: 1,
    product_id: null,
    transaction_type: 'cash-diya',
    liya_diya: 'diya',
    product_name: 'CASH PAID',
    gross_weight: 0,
    net_weight: 0,
    melting: 0,
    rate: 0,
    cash_charge_amount: 200000,
    bill_charge_amount: 0,
    ten_gram_bill_995_rate: 0,
    ten_gram_cash_995_rate: 0,
    purity_999: 0,
    purity_995: 0,
    gst_bhav: 0,
    cash_bhav: 0,
    cash_amount: -200000,
    rtgs_amount: 0,
    balance_cat_995: 0,
    transaction_date: new Date().toISOString().split('T')[0],
    notes: 'Part cash settlement',
    deleted_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
  {
    id: 3,
    vyapari_id: 2,
    product_id: null,
    transaction_type: 'fine-diya',
    liya_diya: 'diya',
    product_name: 'FINE DIYA',
    gross_weight: -25.0,
    net_weight: -25.0,
    melting: 99.5,
    rate: 0,
    cash_charge_amount: 0,
    bill_charge_amount: 0,
    ten_gram_bill_995_rate: 0,
    ten_gram_cash_995_rate: 0,
    purity_999: -24.875,
    purity_995: -25.0,
    gst_bhav: 0,
    cash_bhav: 0,
    cash_amount: 0,
    rtgs_amount: 0,
    balance_cat_995: 25.0,
    transaction_date: new Date().toISOString().split('T')[0],
    notes: 'Pure bar given',
    deleted_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
];

const TYPES: Record<string, string> = {
  'maal-liya': 'Maal Liya',
  'maal-return': 'Maal Return',
  'fine-diya': 'Fine Diya',
  'cash-diya': 'Cash Diya',
  'bank-payment-diya': 'Bank Payment Diya',
  'cash-rate-cut': 'Cash Rate Cut',
  'bill-rate-cut': 'Bill Rate Cut',
  'opening-balance': 'Opening Balance',
};

// Calculation engine matching Yii/PHP TransactionController
function calculatePayload(input: any): Omit<Transaction, 'id' | 'created_at' | 'updated_at' | 'deleted_at'> {
  const type = input.transaction_type || 'maal-liya';
  let net = parseFloat(input.net_weight) || 0;
  let gross = parseFloat(input.gross_weight) || 0;
  let melting = parseFloat(input.melting) || 0;
  let rate = ['fine-diya', 'cash-rate-cut', 'bill-rate-cut'].includes(type) ? 0.0 : (parseFloat(input.rate) || 0);
  let cashCharge = parseFloat(input.cash_charge_amount) || 0;
  let billCharge = parseFloat(input.bill_charge_amount) || 0;
  let bill995 = parseFloat(input.ten_gram_bill_995_rate) || 0;
  let cash995 = parseFloat(input.ten_gram_cash_995_rate) || 0;

  let purity999 = ((melting + rate) * net) / 100;
  let purity995 = purity999 / 0.995;
  let gstBhav = bill995 / 10 + (((bill995 / 100) * 3) / 10);
  let cashBhav = cash995 / 10;
  let cashAmount = 0.0;
  let rtgsAmount = 0.0;
  let balanceCat995 = 0.0;
  let side: 'liya' | 'diya' = 'liya';

  if (type === 'maal-liya') {
    cashAmount = cashBhav * purity995 + cashCharge;
    rtgsAmount = gstBhav * purity995 + billCharge;
    balanceCat995 = (cashAmount + rtgsAmount !== 0.0) ? 0.0 : purity995;
  } else if (type === 'maal-return') {
    side = 'diya';
    gross = -Math.abs(gross);
    net = -Math.abs(net);
    purity999 = -Math.abs(purity999);
    purity995 = -Math.abs(purity995);
    cashAmount = -Math.abs(cashBhav * Math.abs(purity995) + cashCharge);
    rtgsAmount = -Math.abs(gstBhav * Math.abs(purity995) + billCharge);
    balanceCat995 = (cashAmount + rtgsAmount !== 0.0) ? 0.0 : purity995;
  } else if (type === 'cash-diya') {
    side = 'diya';
    cashAmount = -Math.abs(cashCharge);
  } else if (type === 'bank-payment-diya') {
    side = 'diya';
    rtgsAmount = -Math.abs(billCharge);
  } else if (type === 'fine-diya') {
    side = 'diya';
    gross = -Math.abs(gross);
    net = -Math.abs(net);
    purity999 = -Math.abs(purity999);
    purity995 = -Math.abs(purity995);
    balanceCat995 = purity995;
  } else if (type === 'cash-rate-cut') {
    cashAmount = cashBhav * purity995 + cashCharge;
    balanceCat995 = -Math.abs(purity995);
  } else if (type === 'bill-rate-cut') {
    rtgsAmount = gstBhav * purity995 + billCharge;
    balanceCat995 = -Math.abs(purity995);
  } else if (type === 'opening-balance') {
    side = input.liya_diya === 'diya' ? 'diya' : 'liya';
    cashAmount = parseFloat(input.cash_amount) || 0;
    rtgsAmount = parseFloat(input.rtgs_amount) || 0;
    balanceCat995 = parseFloat(input.balance_cat_995) || 0;
  }

  const systemNames: Record<string, string> = {
    'fine-diya': 'FINE DIYA',
    'cash-diya': 'CASH PAID',
    'bank-payment-diya': 'RTGS DONE',
    'cash-rate-cut': 'CASH CUT FINE JAMA',
    'bill-rate-cut': 'BILL CUT FINE JAMA',
  };

  const defaultName = systemNames[type] || TYPES[type] || type;

  return {
    vyapari_id: parseInt(input.vyapari_id || '0', 10),
    product_id: input.product_id ? parseInt(input.product_id, 10) : null,
    transaction_type: type,
    liya_diya: side,
    product_name: String(input.product_name || defaultName).trim(),
    gross_weight: Math.round(gross * 1000) / 1000,
    net_weight: Math.round(net * 1000) / 1000,
    melting: Math.round(melting * 1000) / 1000,
    rate: Math.round(rate * 1000) / 1000,
    cash_charge_amount: Math.round(cashCharge * 100) / 100,
    bill_charge_amount: Math.round(billCharge * 100) / 100,
    ten_gram_bill_995_rate: Math.round(bill995 * 100) / 100,
    ten_gram_cash_995_rate: Math.round(cash995 * 100) / 100,
    purity_999: Math.round(purity999 * 1000) / 1000,
    purity_995: Math.round(purity995 * 1000) / 1000,
    gst_bhav: Math.round(gstBhav * 100) / 100,
    cash_bhav: Math.round(cashBhav * 100) / 100,
    cash_amount: Math.round(cashAmount * 100) / 100,
    rtgs_amount: Math.round(rtgsAmount * 100) / 100,
    balance_cat_995: Math.round(balanceCat995 * 1000) / 1000,
    transaction_date: input.transaction_date || new Date().toISOString().split('T')[0],
    notes: String(input.notes || '').trim(),
  };
}

// Ledger Service Helpers
function getLedgerTotals(vyapariId: number, startDate?: string | null, endDate?: string | null) {
  let list = transactions.filter((t) => !t.deleted_at && t.vyapari_id === vyapariId);
  if (startDate) list = list.filter((t) => t.transaction_date >= startDate);
  if (endDate) list = list.filter((t) => t.transaction_date <= endDate);

  const ntwt = list.reduce((acc, t) => acc + (Number(t.net_weight) || 0), 0);
  const cash = list.reduce((acc, t) => acc + (Number(t.cash_amount) || 0), 0);
  const rtgs = list.reduce((acc, t) => acc + (Number(t.rtgs_amount) || 0), 0);
  const cat_995 = list.reduce((acc, t) => acc + (Number(t.balance_cat_995) || 0), 0);
  const gold_999 = list.reduce((acc, t) => acc + ((Number(t.balance_cat_995) || 0) * 0.995), 0);

  return { ntwt, cash, rtgs, cat_995, gold_999 };
}

function getLedgerSideTotals(vyapariId: number, side: 'liya' | 'diya', startDate?: string | null, endDate?: string | null) {
  let list = transactions.filter((t) => !t.deleted_at && t.vyapari_id === vyapariId && t.liya_diya === side);
  if (startDate) list = list.filter((t) => t.transaction_date >= startDate);
  if (endDate) list = list.filter((t) => t.transaction_date <= endDate);

  const ntwt = list.reduce((acc, t) => acc + (Number(t.net_weight) || 0), 0);
  const cash = list.reduce((acc, t) => acc + (Number(t.cash_amount) || 0), 0);
  const rtgs = list.reduce((acc, t) => acc + (Number(t.rtgs_amount) || 0), 0);
  const cat_995 = list.reduce((acc, t) => acc + (Number(t.balance_cat_995) || 0), 0);
  const gold_999 = list.reduce((acc, t) => acc + ((Number(t.balance_cat_995) || 0) * 0.995), 0);

  return { ntwt, cash, rtgs, cat_995, gold_999 };
}

function getDashboardTotals() {
  return vyaparis
    .filter((v) => !v.deleted_at)
    .map((v) => {
      const vTx = transactions.filter((t) => !t.deleted_at && t.vyapari_id === v.id);
      const cash = vTx.reduce((acc, t) => acc + (Number(t.cash_amount) || 0), 0);
      const rtgs = vTx.reduce((acc, t) => acc + (Number(t.rtgs_amount) || 0), 0);
      const gold_999 = vTx.reduce((acc, t) => acc + ((Number(t.balance_cat_995) || 0) * 0.995), 0);
      return {
        id: v.id,
        vyapari_name: v.vyapari_name,
        company_name: v.company_name,
        primary_number: v.primary_number,
        city: v.city,
        cash,
        rtgs,
        gold_999,
      };
    })
    .sort((a, b) => a.vyapari_name.localeCompare(b.vyapari_name));
}

// ----------------------------------------------------
// Authentication Routes
// ----------------------------------------------------
app.get('/login', (req: Request, res: Response) => {
  if (req.session.user) {
    return res.redirect('/');
  }
  res.render('auth/login', {
    title: 'Login',
    email: req.query.email || '',
    password: '',
  });
});

app.post('/login', (req: Request, res: Response) => {
  const email = String(req.body.email || '').trim();
  const password = String(req.body.password || '').trim();

  // If email is provided, log user in!
  if (email) {
    const defaultAdminEmail = process.env.ADMIN_EMAIL || 'admin@example.com';
    let name = process.env.ADMIN_NAME || 'Admin';

    if (email.toLowerCase() !== defaultAdminEmail.toLowerCase()) {
      const prefix = email.split('@')[0];
      name = prefix.charAt(0).toUpperCase() + prefix.slice(1);
    }

    const user = { name, email };
    req.session.user = user;

    // Set fallback auth cookie for iframe compatibility
    const token = Buffer.from(JSON.stringify(user)).toString('base64');
    res.cookie('auth_user', token, {
      maxAge: 30 * 24 * 60 * 60 * 1000,
      sameSite: 'none',
      secure: true,
      path: '/',
    });

    setFlash(req, 'success', `Logged in successfully as ${name}.`);

    return req.session.save((err) => {
      if (err) console.error('Session save error:', err);
      res.redirect(`/?auth=${token}`);
    });
  }

  setFlash(req, 'error', 'Please enter your email to login.');
  res.redirect('/login');
});

app.post('/logout', (req: Request, res: Response) => {
  delete req.session.user;
  res.clearCookie('auth_user', { path: '/', sameSite: 'none', secure: true });
  res.clearCookie('connect.sid', { path: '/', sameSite: 'none', secure: true });
  req.session.destroy((err) => {
    if (err) console.error('Session destroy error:', err);
    res.redirect('/login');
  });
});

// ----------------------------------------------------
// Dashboard Route
// ----------------------------------------------------
app.get('/', requireLogin, (req: Request, res: Response) => {
  const rows = getDashboardTotals();
  const vyapariMap = new Map<number, string>(
    vyaparis.filter((v) => !v.deleted_at).map((v) => [v.id, v.vyapari_name])
  );
  const recentTransactions = transactions
    .filter((t) => !t.deleted_at)
    .sort((a, b) => b.transaction_date.localeCompare(a.transaction_date) || b.id - a.id)
    .slice(0, 10)
    .map((t) => ({
      ...t,
      vyapari_name: vyapariMap.get(t.vyapari_id) || `Vyapari #${t.vyapari_id}`,
      type_label: TYPES[t.transaction_type] || t.transaction_type,
    }));

  res.render('dashboard/index', {
    title: 'Dashboard',
    rows,
    recentTransactions,
  });
});

// ----------------------------------------------------
// Vyapari Routes
// ----------------------------------------------------
app.get('/vyaparis', requireLogin, (req: Request, res: Response) => {
  const q = String(req.query.q || '').trim().toLowerCase();
  let list = vyaparis.filter((v) => !v.deleted_at);
  if (q) {
    list = list.filter(
      (v) =>
        v.vyapari_name.toLowerCase().includes(q) ||
        v.company_name.toLowerCase().includes(q) ||
        v.city.toLowerCase().includes(q) ||
        v.primary_number.includes(q)
    );
  }
  list.sort((a, b) => a.vyapari_name.localeCompare(b.vyapari_name));

  const balances = getDashboardTotals();
  const balancesById: Record<number, { cash: number; rtgs: number; gold_999: number }> = {};
  balances.forEach((b) => {
    balancesById[b.id] = b;
  });

  res.render('vyaparis/index', {
    title: 'Vyaparis',
    vyaparis: list,
    balances,
    balancesById,
    q,
  });
});

app.get('/vyaparis/create', requireLogin, (req: Request, res: Response) => {
  res.render('vyaparis/form', {
    title: 'New Vyapari',
    vyapari: {},
  });
});

app.post('/vyaparis/store', requireLogin, (req: Request, res: Response) => {
  const now = new Date().toISOString();
  const newVyapari: Vyapari = {
    id: nextVyapariId++,
    vyapari_name: String(req.body.vyapari_name || '').trim(),
    company_name: String(req.body.company_name || '').trim(),
    email_id: String(req.body.email_id || '').trim(),
    primary_number: String(req.body.primary_number || '').trim(),
    secondary_number: String(req.body.secondary_number || '').trim(),
    gst_number: String(req.body.gst_number || '').trim(),
    city: String(req.body.city || '').trim(),
    state: String(req.body.state || '').trim(),
    deleted_at: null,
    created_at: now,
    updated_at: now,
  };
  vyaparis.push(newVyapari);
  setFlash(req, 'success', 'Vyapari saved successfully.');
  res.redirect('/vyaparis');
});

app.get('/vyaparis/edit', requireLogin, (req: Request, res: Response) => {
  const id = parseInt(String(req.query.id), 10);
  const found = vyaparis.find((v) => v.id === id && !v.deleted_at);
  if (!found) {
    setFlash(req, 'error', 'Vyapari not found.');
    return res.redirect('/vyaparis');
  }
  res.render('vyaparis/form', {
    title: 'Edit Vyapari',
    vyapari: found,
  });
});

app.post('/vyaparis/update', requireLogin, (req: Request, res: Response) => {
  const id = parseInt(String(req.body.id), 10);
  const found = vyaparis.find((v) => v.id === id && !v.deleted_at);
  if (!found) {
    setFlash(req, 'error', 'Vyapari not found.');
    return res.redirect('/vyaparis');
  }

  found.vyapari_name = String(req.body.vyapari_name || '').trim();
  found.company_name = String(req.body.company_name || '').trim();
  found.email_id = String(req.body.email_id || '').trim();
  found.primary_number = String(req.body.primary_number || '').trim();
  found.secondary_number = String(req.body.secondary_number || '').trim();
  found.gst_number = String(req.body.gst_number || '').trim();
  found.city = String(req.body.city || '').trim();
  found.state = String(req.body.state || '').trim();
  found.updated_at = new Date().toISOString();

  setFlash(req, 'success', 'Vyapari updated successfully.');
  res.redirect('/vyaparis');
});

app.post('/vyaparis/delete', requireLogin, (req: Request, res: Response) => {
  const id = parseInt(String(req.body.id), 10);
  const found = vyaparis.find((v) => v.id === id && !v.deleted_at);
  if (found) {
    found.deleted_at = new Date().toISOString();
    setFlash(req, 'success', 'Vyapari deleted successfully.');
  }
  res.redirect('/vyaparis');
});

app.post('/vyaparis/settle', requireLogin, (req: Request, res: Response) => {
  const vyapariId = parseInt(String(req.body.vyapari_id), 10);
  transactions = transactions.filter((t) => t.vyapari_id !== vyapariId);
  setFlash(req, 'success', 'All transactions for this vyapari were settled.');
  res.redirect(`/ledger?vyapari_id=${vyapariId}`);
});

app.post('/vyaparis/roundoff', requireLogin, (req: Request, res: Response) => {
  const vyapariId = parseInt(String(req.body.vyapari_id), 10);
  const date = String(req.body.end_date || new Date().toISOString().split('T')[0]);

  transactions = transactions.filter(
    (t) => !(t.vyapari_id === vyapariId && t.transaction_date >= date)
  );

  const entries: [string, number, string][] = [
    ['cash_amount', parseFloat(req.body.grandtotal_cash) || 0, 'CASH'],
    ['rtgs_amount', parseFloat(req.body.grandtotal_rtgs) || 0, 'RTGS'],
    ['balance_cat_995', parseFloat(req.body.grandtotal_cat_995) || 0, 'CAT_995'],
  ];

  for (const [field, amount, label] of entries) {
    if (amount === 0) continue;
    const now = new Date().toISOString();
    const newTx: Transaction = {
      id: nextTransactionId++,
      vyapari_id: vyapariId,
      product_id: null,
      transaction_type: 'opening-balance',
      liya_diya: amount <= 0 ? 'diya' : 'liya',
      product_name: `Roundoff carry forward ${label}`,
      gross_weight: 0,
      net_weight: 0,
      melting: 0,
      rate: 0,
      cash_charge_amount: 0,
      bill_charge_amount: 0,
      ten_gram_bill_995_rate: 0,
      ten_gram_cash_995_rate: 0,
      purity_999: 0,
      purity_995: 0,
      gst_bhav: 0,
      cash_bhav: 0,
      cash_amount: field === 'cash_amount' ? amount : 0,
      rtgs_amount: field === 'rtgs_amount' ? amount : 0,
      balance_cat_995: field === 'balance_cat_995' ? amount : 0,
      transaction_date: date,
      notes: 'Roundoff carry forward auto-generated entry',
      deleted_at: null,
      created_at: now,
      updated_at: now,
    };
    transactions.push(newTx);
  }

  setFlash(req, 'success', 'Balances carried forward.');
  res.redirect(`/ledger?vyapari_id=${vyapariId}&end_date=${date}`);
});

// ----------------------------------------------------
// Product Routes
// ----------------------------------------------------
app.get('/products', requireLogin, (req: Request, res: Response) => {
  const activeVyaparis = vyaparis.filter((v) => !v.deleted_at);
  const vyapariMap = new Map<number, string>(activeVyaparis.map((v) => [v.id, v.vyapari_name]));

  const list = products
    .filter((p) => !p.deleted_at)
    .map((p) => ({
      ...p,
      vyapari_name: p.vyapari_id ? vyapariMap.get(p.vyapari_id) || 'Common' : 'Common',
    }))
    .sort((a, b) => a.product_name.localeCompare(b.product_name));

  res.render('products/index', {
    title: 'Products',
    products: list,
  });
});

app.get('/products/create', requireLogin, (req: Request, res: Response) => {
  const activeVyaparis = vyaparis
    .filter((v) => !v.deleted_at)
    .sort((a, b) => a.vyapari_name.localeCompare(b.vyapari_name));

  res.render('products/form', {
    title: 'New Product',
    product: {},
    vyaparis: activeVyaparis,
    selectedVyapariId: req.query.vyapari_id || '',
  });
});

app.post('/products/store', requireLogin, (req: Request, res: Response) => {
  const now = new Date().toISOString();
  const newProduct: Product = {
    id: nextProductId++,
    vyapari_id: req.body.vyapari_id ? parseInt(req.body.vyapari_id, 10) : null,
    product_name: String(req.body.product_name || '').trim(),
    purity: parseFloat(req.body.purity) || 0,
    rate: parseFloat(req.body.rate) || 0,
    deleted_at: null,
    created_at: now,
    updated_at: now,
  };
  products.push(newProduct);
  setFlash(req, 'success', 'Product saved successfully.');
  res.redirect('/products');
});

app.get('/products/edit', requireLogin, (req: Request, res: Response) => {
  const id = parseInt(String(req.query.id), 10);
  const found = products.find((p) => p.id === id && !p.deleted_at);
  if (!found) {
    setFlash(req, 'error', 'Product not found.');
    return res.redirect('/products');
  }
  const activeVyaparis = vyaparis
    .filter((v) => !v.deleted_at)
    .sort((a, b) => a.vyapari_name.localeCompare(b.vyapari_name));

  res.render('products/form', {
    title: 'Edit Product',
    product: found,
    vyaparis: activeVyaparis,
    selectedVyapariId: found.vyapari_id || '',
  });
});

app.post('/products/update', requireLogin, (req: Request, res: Response) => {
  const id = parseInt(String(req.body.id), 10);
  const found = products.find((p) => p.id === id && !p.deleted_at);
  if (!found) {
    setFlash(req, 'error', 'Product not found.');
    return res.redirect('/products');
  }

  found.vyapari_id = req.body.vyapari_id ? parseInt(req.body.vyapari_id, 10) : null;
  found.product_name = String(req.body.product_name || '').trim();
  found.purity = parseFloat(req.body.purity) || 0;
  found.rate = parseFloat(req.body.rate) || 0;
  found.updated_at = new Date().toISOString();

  setFlash(req, 'success', 'Product updated successfully.');
  res.redirect('/products');
});

app.post('/products/delete', requireLogin, (req: Request, res: Response) => {
  const id = parseInt(String(req.body.id), 10);
  const found = products.find((p) => p.id === id && !p.deleted_at);
  if (found) {
    found.deleted_at = new Date().toISOString();
    setFlash(req, 'success', 'Product deleted successfully.');
  }
  res.redirect('/products');
});

// ----------------------------------------------------
// Transaction Routes
// ----------------------------------------------------
app.get('/transactions', requireLogin, (req: Request, res: Response) => {
  const filters = {
    vyapari_id: req.query.vyapari_id ? String(req.query.vyapari_id) : '',
    start_date: req.query.start_date ? String(req.query.start_date) : '',
    end_date: req.query.end_date ? String(req.query.end_date) : '',
  };

  const activeVyaparis = vyaparis
    .filter((v) => !v.deleted_at)
    .sort((a, b) => a.vyapari_name.localeCompare(b.vyapari_name));
  const vyapariMap = new Map<number, string>(activeVyaparis.map((v) => [v.id, v.vyapari_name]));

  let list = transactions.filter((t) => !t.deleted_at);
  if (filters.vyapari_id) {
    const vid = parseInt(filters.vyapari_id, 10);
    list = list.filter((t) => t.vyapari_id === vid);
  }
  if (filters.start_date) {
    list = list.filter((t) => t.transaction_date >= filters.start_date);
  }
  if (filters.end_date) {
    list = list.filter((t) => t.transaction_date <= filters.end_date);
  }
  list.sort((a, b) => b.transaction_date.localeCompare(a.transaction_date) || b.id - a.id);

  const rows = list.map((t) => ({
    ...t,
    vyapari_name: vyapariMap.get(t.vyapari_id) || `Vyapari #${t.vyapari_id}`,
  }));

  res.render('transactions/index', {
    title: 'Transactions',
    transactions: rows,
    vyaparis: activeVyaparis,
    filters,
    types: TYPES,
  });
});

app.get('/transactions/create', requireLogin, (req: Request, res: Response) => {
  const activeVyaparis = vyaparis
    .filter((v) => !v.deleted_at)
    .sort((a, b) => a.vyapari_name.localeCompare(b.vyapari_name));
  const vyapariMap = new Map<number, string>(activeVyaparis.map((v) => [v.id, v.vyapari_name]));

  const activeProducts = products
    .filter((p) => !p.deleted_at)
    .map((p) => ({
      ...p,
      vyapari_name: p.vyapari_id ? vyapariMap.get(p.vyapari_id) || '' : '',
    }));

  const type = String(req.query.type || 'maal-liya');
  const vyapariId = req.query.vyapari_id ? String(req.query.vyapari_id) : '';

  res.render('transactions/form', {
    title: 'New Transaction',
    transaction: {
      transaction_type: type,
      vyapari_id: vyapariId,
      transaction_date: new Date().toISOString().split('T')[0],
    },
    vyaparis: activeVyaparis,
    products: activeProducts,
    types: TYPES,
    currentType: type,
    selectedVyapariId: vyapariId,
  });
});

app.post('/transactions/store', requireLogin, (req: Request, res: Response) => {
  const payload = calculatePayload(req.body);
  const now = new Date().toISOString();

  if (['cash-rate-cut', 'bill-rate-cut'].includes(payload.transaction_type)) {
    // Row 1: Fine diya row
    const row1: Transaction = {
      id: nextTransactionId++,
      ...payload,
      liya_diya: 'diya',
      gross_weight: -Math.abs(payload.gross_weight),
      net_weight: -Math.abs(payload.net_weight),
      cash_charge_amount: 0,
      bill_charge_amount: 0,
      ten_gram_bill_995_rate: 0,
      ten_gram_cash_995_rate: 0,
      gst_bhav: 0,
      cash_bhav: 0,
      cash_amount: 0,
      rtgs_amount: 0,
      deleted_at: null,
      created_at: now,
      updated_at: now,
    };
    transactions.push(row1);

    // Row 2: Money jama row
    const row2: Transaction = {
      id: nextTransactionId++,
      ...payload,
      liya_diya: 'liya',
      net_weight: 0,
      melting: 0,
      balance_cat_995: 0,
      deleted_at: null,
      created_at: now,
      updated_at: now,
    };
    transactions.push(row2);
  } else {
    const tx: Transaction = {
      id: nextTransactionId++,
      ...payload,
      deleted_at: null,
      created_at: now,
      updated_at: now,
    };
    transactions.push(tx);
  }

  setFlash(req, 'success', 'Transaction saved successfully.');
  res.redirect(payload.vyapari_id ? `/ledger?vyapari_id=${payload.vyapari_id}` : '/transactions');
});

app.get('/transactions/edit', requireLogin, (req: Request, res: Response) => {
  const id = parseInt(String(req.query.id), 10);
  const found = transactions.find((t) => t.id === id && !t.deleted_at);
  if (!found) {
    setFlash(req, 'error', 'Transaction not found.');
    return res.redirect('/transactions');
  }

  const activeVyaparis = vyaparis
    .filter((v) => !v.deleted_at)
    .sort((a, b) => a.vyapari_name.localeCompare(b.vyapari_name));
  const vyapariMap = new Map<number, string>(activeVyaparis.map((v) => [v.id, v.vyapari_name]));

  const activeProducts = products
    .filter((p) => !p.deleted_at)
    .map((p) => ({
      ...p,
      vyapari_name: p.vyapari_id ? vyapariMap.get(p.vyapari_id) || '' : '',
    }));

  res.render('transactions/form', {
    title: 'Edit Transaction',
    transaction: found,
    vyaparis: activeVyaparis,
    products: activeProducts,
    types: TYPES,
    currentType: found.transaction_type,
    selectedVyapariId: found.vyapari_id,
  });
});

app.post('/transactions/update', requireLogin, (req: Request, res: Response) => {
  const id = parseInt(String(req.body.id), 10);
  const found = transactions.find((t) => t.id === id && !t.deleted_at);
  if (!found) {
    setFlash(req, 'error', 'Transaction not found.');
    return res.redirect('/transactions');
  }

  const payload = calculatePayload(req.body);
  Object.assign(found, payload, { updated_at: new Date().toISOString() });

  setFlash(req, 'success', 'Transaction updated successfully.');
  res.redirect(`/ledger?vyapari_id=${found.vyapari_id}`);
});

app.post('/transactions/delete', requireLogin, (req: Request, res: Response) => {
  const id = parseInt(String(req.body.id), 10);
  const found = transactions.find((t) => t.id === id && !t.deleted_at);
  const vyapariId = found ? found.vyapari_id : 0;
  if (found) {
    found.deleted_at = new Date().toISOString();
    setFlash(req, 'success', 'Transaction deleted successfully.');
  }
  res.redirect(vyapariId ? `/ledger?vyapari_id=${vyapariId}` : '/transactions');
});

// ----------------------------------------------------
// Ledger Route
// ----------------------------------------------------
app.get('/ledger', requireLogin, (req: Request, res: Response) => {
  const activeVyaparis = vyaparis
    .filter((v) => !v.deleted_at)
    .sort((a, b) => a.vyapari_name.localeCompare(b.vyapari_name));

  const vyapariId = parseInt(String(req.query.vyapari_id || (activeVyaparis[0]?.id ?? 0)), 10);
  const start = req.query.start_date ? String(req.query.start_date) : null;
  const end = req.query.end_date ? String(req.query.end_date) : new Date().toISOString().split('T')[0];

  const currentVyapari = activeVyaparis.find((v) => v.id === vyapariId) || null;

  // Opening totals: up to start date - 1 day
  let opening = { ntwt: 0, cash: 0, rtgs: 0, cat_995: 0, gold_999: 0 };
  if (start) {
    const prevDay = new Date(start);
    prevDay.setDate(prevDay.getDate() - 1);
    const prevDayStr = prevDay.toISOString().split('T')[0];
    opening = getLedgerTotals(vyapariId, null, prevDayStr);
  }

  const closing = getLedgerTotals(vyapariId, null, end);
  const liya = getLedgerSideTotals(vyapariId, 'liya', start, end);
  const diya = getLedgerSideTotals(vyapariId, 'diya', start, end);

  let ledgerTx = transactions.filter((t) => !t.deleted_at && t.vyapari_id === vyapariId);
  if (start) ledgerTx = ledgerTx.filter((t) => t.transaction_date >= start);
  if (end) ledgerTx = ledgerTx.filter((t) => t.transaction_date <= end);
  ledgerTx.sort((a, b) => b.transaction_date.localeCompare(a.transaction_date) || b.id - a.id);

  res.render('ledger/show', {
    title: 'Ledger',
    vyaparis: activeVyaparis,
    vyapariId,
    currentVyapari,
    start,
    end,
    opening,
    closing,
    liya,
    diya,
    transactions: ledgerTx,
  });
});

// Fallback error handler
app.use((err: Error, req: Request, res: Response, next: NextFunction) => {
  console.error('[Vyapari App Error]:', err);
  res.status(500).send(`Server Error: ${err.message}`);
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Vyapari app running on http://0.0.0.0:${PORT}`);
});
