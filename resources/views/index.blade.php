<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Laundry Batch Recording Form</title>
  <style>
    :root { --bg:#0f172a; --card:#111827; --muted:#6b7280; --text:#e5e7eb; --accent:#22c55e; --border:#1f2937; }
    * { box-sizing: border-box; }
    body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background:linear-gradient(180deg,#0b1020,#0f172a); color:var(--text); }
    .wrap { max-width: 880px; margin: 40px auto; padding: 0 16px; }
    .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,.35); }
    h1 { margin: 0 0 6px; font-size: 22px; }
    p.desc { margin: 0 0 16px; color: var(--muted); }
    .grid { display: grid; gap: 12px; grid-template-columns: 1fr 1fr; }
    .grid-3 { display: grid; gap: 12px; grid-template-columns: 2fr 1fr 1fr; align-items: center; }
    @media (max-width: 760px){ .grid, .grid-3 { grid-template-columns: 1fr; } }
    label { display:block; font-size: 13px; color: var(--muted); margin-bottom: 6px; }
    input[type="text"], input[type="number"], input[type="email"], input[type="datetime-local"], select, textarea {
      width:100%; padding: 10px 12px; border-radius: 10px; border:1px solid var(--border); background:#0b1222; color: var(--text);
      outline: none; transition: border-color .2s, box-shadow .2s;
    }
    input:focus, select:focus, textarea:focus { border-color:#334155; box-shadow: 0 0 0 4px rgba(51,65,85,.25); }
    .row { padding: 10px 12px; border: 1px dashed #243043; border-radius: 12px; background:#0b1222; }
    .muted { color: var(--muted); font-size: 13px; }
    .total { display:flex; justify-content: space-between; align-items:center; margin-top: 8px; padding: 10px 12px; background:#0b1222; border:1px solid var(--border); border-radius: 12px; }
    .total strong { font-size: 16px; }
    .btns { display:flex; gap: 10px; margin-top: 18px; }
    button, .btn {
      display:inline-flex; align-items:center; gap: 8px; padding: 10px 14px; border-radius: 12px; border:1px solid transparent; cursor:pointer; font-weight:600;
      background: var(--accent); color: #08120d; transition: transform .06s ease, filter .2s ease; text-decoration:none;
    }
    button:hover, .btn:hover { filter: brightness(1.05); }
    button:active, .btn:active { transform: translateY(1px); }
    .btn-secondary { background: transparent; border-color:#334155; color: var(--text); }
    .items-table { display: grid; gap: 8px; }
    .items-table .grid-3 > div:first-child { font-weight: 600; }
    .inline { display:flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .inline > * { flex: 1 1 180px; }
    .right { text-align: right; }
    .badge { font-size: 12px; padding: 3px 8px; border-radius: 999px; background:#0b1222; border:1px solid var(--border); color:var(--muted); }
  /* Alerts */
  .alert { position: relative; display:flex; align-items:center; justify-content:space-between; padding: 10px 12px; border-radius: 10px; margin-bottom: 12px; border: 1px solid transparent; }
  .alert-body { color: var(--text); font-weight:600; }
  .alert-success { background: linear-gradient(90deg, rgba(34,197,94,0.12), rgba(34,197,94,0.04)); border-color: rgba(34,197,94,0.25); }
  .alert-error { background: linear-gradient(90deg, rgba(239,68,68,0.08), rgba(239,68,68,0.04)); border-color: rgba(239,68,68,0.25); }
  .alert-close { background: transparent; border: none; color: var(--text); font-size: 18px; cursor: pointer; padding: 4px 8px; border-radius: 8px; }
  .alert-close:hover { background: rgba(255,255,255,0.03); }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <!-- Alerts -->
      @if(session('success') || session('error'))
        <div class="alert {{ session('success') ? 'alert-success' : 'alert-error' }}" id="flashAlert">
          <div class="alert-body">
            {{ session('success') ?? session('error') }}
          </div>
          <button type="button" class="alert-close" id="alertClose">×</button>
        </div>
      @endif
      <h1>Laundry Batch Recording</h1>
      <p class="desc">Simple form to enter laundry quantity & costs. When submitted, the system will include current date/time.</p>
      <!-- FORM SUBMISSION CONFIGURATION
        1) NO BACKEND NEEDED (quick): replace action with your Formspree/EmailJS endpoint
           <form id="laundryForm" action="https://formspree.io/f/XXXXXXX" method="POST">
        2) WITH PHP BACKEND: keep action="process.php" and create process.php to send email.
      -->
      <form id="laundryForm" action="{{ route('submit') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="submitted_at" id="submitted_at" />
        <div class="grid">
          <div>
            <label for="staff_name">Staff Name</label>
            <input type="text" id="staff_name" name="staff_name" placeholder="e.g., John Smith" required />
          </div>
          <div>
            <label for="location">Area/Branch (optional)</label>
            <input type="text" id="location" name="location" placeholder="e.g., Branch 2" />
          </div>
        </div>

        <div style="margin-top:14px">
          <label for="recipient_email">Recipient Email</label>
          <input type="email" id="recipient_email" name="recipient_email" placeholder="e.g., manager@company.com" required />
        </div>

        <div class="row" style="margin-top:14px">
          <div class="items-table">
            <div class="grid-3 muted">
              <div>Items</div>
              <div>Quantity</div>
              <div class="right">Notes (optional)</div>
            </div>

            <div class="grid-3">
              <div>Bed Sheets</div>
              <div><input type="number" min="0" step="1" name="sheets" value="0" inputmode="numeric" /></div>
              <div><input type="text" name="sheets_notes" placeholder="e.g., queen size" /></div>
            </div>

            <div class="grid-3">
              <div>Pillowcases</div>
              <div><input type="number" min="0" step="1" name="pillowcases" value="0" inputmode="numeric" /></div>
              <div><input type="text" name="pillowcases_notes" placeholder="e.g., cotton type" /></div>
            </div>

            <div class="grid-3">
              <div>Duvet Covers</div>
              <div><input type="number" min="0" step="1" name="duvets" value="0" inputmode="numeric" /></div>
              <div><input type="text" name="duvets_notes" placeholder=" " /></div>
            </div>

            <div class="grid-3">
              <div>Towels</div>
              <div><input type="number" min="0" step="1" name="towels" value="0" inputmode="numeric" /></div>
              <div><input type="text" name="towels_notes" placeholder=" " /></div>
            </div>
          </div>

          <div class="total" id="itemsTotalBox">
            <span class="badge">Total Items</span>
            <strong><span id="items_total">0</span></strong>
          </div>
        </div>

        <div class="grid" style="margin-top:14px">
          <div>
            <label for="wash_cost">Wash Cost (₫ / $)</label>
            <input type="number" min="0" step="0.01" id="wash_cost" name="wash_cost" value="0" required />
          </div>
          <div>
            <label for="dry_cost">Dry Cost (₫ / $)</label>
            <input type="number" min="0" step="0.01" id="dry_cost" name="dry_cost" value="0" required />
          </div>
        </div>

        <div class="total">
          <span class="badge">Total Cost</span>
          <strong><span id="cost_total">0</span></strong>
        </div>

        <div style="margin-top:14px">
          <label for="notes">General Notes (optional)</label>
          <textarea id="notes" name="notes" rows="3" placeholder="e.g., used machine #3, added 2 coins for drying, etc."></textarea>
        </div>

        <div class="btns">
          <button type="submit">Submit</button>
          <a class="btn btn-secondary" href="#" id="resetBtn">Quick Reset</a>
        </div>

        <p class="muted" style="margin-top:10px">
          When submitted, the form will include the current <strong>date/time</strong> (from your device) and the total quantity/cost above.
        </p>
      </form>
    </div>
  </div>

  <script>
    const form = document.getElementById('laundryForm');
    const submittedAt = document.getElementById('submitted_at');
    const qtyInputs = Array.from(form.querySelectorAll('input[type="number"][name$="s"]'));
    const itemsTotalEl = document.getElementById('items_total');
    const costTotalEl  = document.getElementById('cost_total');

    const washCost = document.getElementById('wash_cost');
    const dryCost  = document.getElementById('dry_cost');

    function fmt(n){
      // Compact display: 1,234.00
      return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function calcTotals(){
      const itemsTotal = qtyInputs.reduce((sum, el) => sum + (parseInt(el.value, 10) || 0), 0);
      itemsTotalEl.textContent = fmt(itemsTotal);

      const total = (parseFloat(washCost.value || 0) + parseFloat(dryCost.value || 0));
      costTotalEl.textContent = fmt(total);
    }

    qtyInputs.forEach(el => el.addEventListener('input', calcTotals));
    [washCost, dryCost].forEach(el => el.addEventListener('input', calcTotals));

    calcTotals();

    document.getElementById('resetBtn').addEventListener('click', (e) => {
      e.preventDefault();
      form.reset();
      calcTotals();
    });

    form.addEventListener('submit', () => {
      const now = new Date();
      submittedAt.value = now.toISOString(); // example: 2025-09-25T05:12:34.567Z
      calcTotals();
    });

    // Auto-hide/dismiss flash alert
    const flash = document.getElementById('flashAlert');
    if (flash) {
      const closeBtn = document.getElementById('alertClose');
      // auto hide after 6s
      setTimeout(() => {
        flash.style.transition = 'opacity 300ms ease, transform 300ms ease';
        flash.style.opacity = '0';
        flash.style.transform = 'translateY(-6px)';
        setTimeout(() => flash.remove(), 350);
      }, 6000);
      if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
    }
  </script>
</body>
</html>
	