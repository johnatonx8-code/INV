<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Branch Model Count</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap">
<style>
:root{
  --bg:#eceff2; --surface:#ffffff; --ink:#14202b; --muted:#5b6b79; --line:#d6dce2;
  --accent:#0b6e5f; --accent-ink:#ffffff; --accent-soft:#dcefeb; --warn:#9a5b00; --warn-soft:#fbefd9;
  --f-display:'Barlow Condensed','Arial Narrow',sans-serif; --f-body:'Barlow',system-ui,sans-serif; --f-mono:'JetBrains Mono',ui-monospace,Consolas,monospace;
}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){
  --bg:#0f161d; --surface:#17212b; --ink:#e7edf2; --muted:#93a3b1; --line:#2a3743;
  --accent:#4fc3ae; --accent-ink:#06241f; --accent-soft:#17352f; --warn:#f0b45a; --warn-soft:#3a2d14; color-scheme:dark}}
:root[data-theme="dark"]{
  --bg:#0f161d; --surface:#17212b; --ink:#e7edf2; --muted:#93a3b1; --line:#2a3743;
  --accent:#4fc3ae; --accent-ink:#06241f; --accent-soft:#17352f; --warn:#f0b45a; --warn-soft:#3a2d14; color-scheme:dark}
*{box-sizing:border-box}
[hidden]{display:none!important}
body{background:var(--bg);color:var(--ink);font-family:var(--f-body);font-size:15px;line-height:1.45;
  padding-block:28px 48px;padding-left:max(16px,env(safe-area-inset-left,0px));padding-right:max(16px,env(safe-area-inset-right,0px))}
.wrap{max-width:1240px;margin:0 auto;display:flex;flex-direction:column;gap:20px}
h1{font-family:var(--f-display);font-weight:700;font-size:34px;letter-spacing:.01em;margin:0;text-wrap:balance}
.sub{color:var(--muted);margin:4px 0 0}
.wrap > header,
.wrap > .drop,
.wrap > #clientmsg,
.wrap > .note,
.wrap > #problems,
.wrap > .bar{width:100%;max-width:760px;margin-left:auto;margin-right:auto}
.wrap > header{text-align:center}
.bar{justify-content:center}
.drop{display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center;padding:26px 16px;border:2px dashed var(--line);
  background:var(--surface);border-radius:6px;transition:border-color .15s,background .15s}
.drop.over{border-color:var(--accent);background:var(--accent-soft)}
.drop b{font-family:var(--f-display);font-size:22px;font-weight:700}
.drop p{margin:0;color:var(--muted);max-width:100ch}
.row{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
button{font:inherit;font-weight:600;cursor:pointer;border-radius:5px;padding:9px 16px;border:1px solid var(--line);background:var(--surface);color:var(--ink)}
button.primary{background:var(--accent);color:var(--accent-ink);border-color:var(--accent)}
button:focus-visible{outline:3px solid var(--accent);outline-offset:2px}
.note{padding:10px 14px;border-radius:5px;background:var(--warn-soft);color:var(--warn);font-weight:500}
.note.ok{background:var(--accent-soft);color:var(--accent)}
#problems{display:flex;flex-direction:column;gap:8px}
.bar{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
.bar .note{flex:1 1 280px}
.cols{display:flex;gap:20px;align-items:flex-start}
.col{flex:1 1 0;min-width:0;display:flex;flex-direction:column;gap:20px}
.card{background:var(--surface);border:1px solid var(--line);border-radius:6px;overflow:hidden}
.head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;padding:16px 16px 12px}
.name{font-family:var(--f-display);font-weight:700;font-size:30px;letter-spacing:.04em;line-height:1;color:var(--ink);background:transparent;border:0;border-bottom:1px dashed var(--line);padding:0 0 2px;width:min(100%,300px);text-transform:uppercase}
.name:focus-visible{outline:none;border-bottom-color:var(--accent)}
.head small{color:var(--muted);display:block;margin-top:4px}
.tools{gap:6px;flex-wrap:nowrap}
.mini{padding:7px 10px;line-height:1}
.mini:disabled{opacity:.35;cursor:default}
.scroll{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-variant-numeric:tabular-nums}
th,td{padding:8px 12px;text-align:left;border-top:1px solid var(--line);white-space:nowrap}
th{font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:600;background:var(--bg)}
td.n,th.n{text-align:right;width:1%}
td.m{font-family:var(--f-mono);font-size:14px}
td.c{color:var(--muted)}
td.t{font-weight:600;font-size:17px}
tfoot td{font-family:var(--f-display);font-size:22px;font-weight:700;background:var(--accent-soft);color:var(--accent);border-top:2px solid var(--accent)}
th.sort{cursor:pointer;user-select:none}
th.sort::after{content:' \2195';opacity:.4}
th.sort[aria-sort="ascending"]::after{content:' \25B2';opacity:1;color:var(--accent)}
th.sort[aria-sort="descending"]::after{content:' \25BC';opacity:1;color:var(--accent)}
th.sort:focus-visible{outline:3px solid var(--accent);outline-offset:-3px}
@media (max-width:860px){.cols{flex-direction:column;align-items:stretch}.col{width:100%}}
</style>
</head>
<body>
<div class="wrap">
  <header>
    <h1>Branch Model Count</h1>
    <p class="sub">Add up to 20 Ending Inventory files. Each file becomes one branch, named after the file. Choose files again to add more branches.</p>
  </header>

  <form class="drop" id="drop" method="POST" action="{{ route('branch-count.store', [], false) }}" enctype="multipart/form-data" tabindex="0">
    @csrf
    <b>Paste or drop the .xlsx files here</b>
    <p>Select several files at once, drag them onto this box, or click this box and press Ctrl+V. Files are counted and then discarded. Nothing is saved.</p>
    <div class="row"><button class="primary" id="pick" type="button">Choose files</button></div>
    <input id="file" type="file" accept=".xlsx" multiple hidden>
  </form>

  <div id="clientmsg" class="note" hidden></div>
  <div id="problems"></div>

  <div class="bar" id="bar" hidden>
    <div class="note ok" id="summary"></div>
    <button type="button" class="primary" id="copyall">Copy all branches</button>
    <button type="button" id="clearall">Clear all</button>
  </div>

  <div class="cols" id="cols"><div class="col"></div><div class="col"></div></div>
</div>

<script>
(function () {
  var MAX = 20;
  var form = document.getElementById('drop'), input = document.getElementById('file');
  var cols = document.querySelectorAll('#cols .col');
  var cards = [];

  function msg(t) {
    var m = document.getElementById('clientmsg');
    m.textContent = t; m.hidden = !t;
  }
  function showProblems(list) {
    var box = document.getElementById('problems');
    box.innerHTML = '';
    (list || []).forEach(function (p) {
      var d = document.createElement('div'); d.className = 'note'; d.textContent = p; box.appendChild(d);
    });
  }

  // ---------- add files (they are added to the branches already on the page) ----------
  function send(files) {
    var list = Array.prototype.filter.call(files || [], function (f) { return /\.xlsx$/i.test(f.name); });
    if (!list.length) { msg('Only .xlsx files can be added.'); return; }
    if (list.length > MAX) { msg('You can add up to ' + MAX + ' files at a time. You selected ' + list.length + '.'); return; }
    if (cards.length + list.length > MAX) {
      msg('The page holds up to ' + MAX + ' branches. You have ' + cards.length + ' and tried to add ' + list.length + '. Remove some first.');
      return;
    }
    msg('Counting ' + list.length + ' file' + (list.length > 1 ? 's' : '') + '...');
    var fd = new FormData();
    fd.append('_token', form.querySelector('input[name="_token"]').value);
    list.forEach(function (f) { fd.append('files[]', f, f.name); });

    fetch(form.getAttribute('action'), {
      method: 'POST', body: fd, credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (r) {
      return r.text().then(function (t) {
        var j = null; try { j = JSON.parse(t); } catch (e) {}
        return { ok: r.ok, status: r.status, j: j };
      });
    }).then(function (res) {
      if (!res.ok) {
        if (res.status === 419) msg('This page expired. Reload it and add the files again.');
        else msg((res.j && res.j.message) || 'The server returned an error (' + res.status + ').');
        return;
      }
      var tmp = document.createElement('div');
      tmp.innerHTML = res.j.html;
      Array.prototype.forEach.call(tmp.querySelectorAll('[data-card]'), function (c) { cards.push(c); });
      layout(); refresh(); showProblems(res.j.problems); msg('');
    }).catch(function () {
      msg('Could not reach the app. Check that the server is still running.');
    });
  }

  document.getElementById('pick').onclick = function () { input.click(); };
  input.onchange = function () { var f = Array.prototype.slice.call(input.files); input.value = ''; if (f.length) send(f); };

  ['dragenter', 'dragover'].forEach(function (ev) {
    form.addEventListener(ev, function (e) { e.preventDefault(); form.classList.add('over'); });
  });
  ['dragleave', 'drop'].forEach(function (ev) {
    form.addEventListener(ev, function (e) { e.preventDefault(); form.classList.remove('over'); });
  });
  form.addEventListener('drop', function (e) { send(e.dataTransfer.files); });
  document.addEventListener('dragover', function (e) { e.preventDefault(); });
  document.addEventListener('drop', function (e) { e.preventDefault(); });
  document.addEventListener('paste', function (e) {
    var files = e.clipboardData && e.clipboardData.files;
    if (files && files.length) { e.preventDefault(); send(files); }
  });

  // ---------- layout: branches fill two columns in turn, like the sheet ----------
  function layout() {
    cards.forEach(function (c, i) { cols[i % 2].appendChild(c); });
    cards.forEach(function (c, i) {
      c.querySelector('.move[data-dir="-1"]').disabled = i === 0;
      c.querySelector('.move[data-dir="1"]').disabled = i === cards.length - 1;
    });
  }
  function refresh() {
    var units = cards.reduce(function (a, c) { return a + Number(c.querySelector('.tot').textContent.trim()); }, 0);
    document.getElementById('bar').hidden = !cards.length;
    document.getElementById('summary').textContent =
      'Counted ' + units + ' units in ' + cards.length + ' ' + (cards.length === 1 ? 'branch' : 'branches') + '.';
  }

  document.addEventListener('click', function (e) {
    var mv = e.target.closest('.move');
    if (mv && !mv.disabled) {
      var card = mv.closest('[data-card]'), i = cards.indexOf(card), j = i + Number(mv.dataset.dir);
      if (j >= 0 && j < cards.length) {
        cards[i] = cards[j]; cards[j] = card;
        layout();
        card.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      }
      return;
    }
    var rm = e.target.closest('.remove');
    if (rm) {
      var c = rm.closest('[data-card]'), k = cards.indexOf(c);
      if (k > -1) { cards.splice(k, 1); c.remove(); layout(); refresh(); }
    }
  });
  document.getElementById('clearall').onclick = function () {
    cards.forEach(function (c) { c.remove(); });
    cards = []; refresh(); showProblems([]); msg('');
  };

  // ---------- sorting (per table) ----------
  function sortTable(th) {
    var table = th.closest('table'), tbody = table.tBodies[0];
    var col = Number(th.dataset.col), num = th.dataset.type === 'num';
    var dir = th.getAttribute('aria-sort') === 'ascending' ? -1 : 1;
    table.querySelectorAll('th.sort').forEach(function (o) { o.setAttribute('aria-sort', 'none'); });
    th.setAttribute('aria-sort', dir === 1 ? 'ascending' : 'descending');
    var opt = { numeric: true, sensitivity: 'base' };
    var rows = Array.prototype.slice.call(tbody.rows).sort(function (a, b) {
      var x = a.cells[col].textContent.trim(), y = b.cells[col].textContent.trim();
      var c = num ? (+x) - (+y) : x.localeCompare(y, undefined, opt);
      if (c !== 0) return c * dir;
      return a.cells[1].textContent.localeCompare(b.cells[1].textContent, undefined, opt) ||
             a.cells[2].textContent.localeCompare(b.cells[2].textContent, undefined, opt);
    });
    rows.forEach(function (r, i) { r.cells[0].textContent = i + 1; tbody.appendChild(r); });
  }
  document.addEventListener('click', function (e) {
    var th = e.target.closest('th.sort');
    if (th) sortTable(th);
  });
  document.addEventListener('keydown', function (e) {
    var th = e.target.closest && e.target.closest('th.sort');
    if (th && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); sortTable(th); }
  });

  // ---------- copy: same grid as the sheet ----------
  // One block per branch: name row, header row, data rows, "Total :" row.
  function blockLines(card) {
    var name = card.querySelector('.name').value.trim().toUpperCase();
    var lines = [[name, '', '', ''], ['#', 'Brand', 'Model', 'Total']];
    Array.prototype.forEach.call(card.querySelectorAll('tbody tr'), function (r) {
      lines.push(Array.prototype.map.call(r.cells, function (c) { return c.textContent.trim(); }));
    });
    lines.push(['Total :', '', '', card.querySelector('.tot').textContent.trim()]);
    return lines;
  }

  // Branches go into two side-by-side columns in turn. A blank row separates stacked blocks
  // and a blank column separates the two columns.
  function gridText(list) {
    var side = [[], []], empty = ['', '', '', ''];
    list.forEach(function (card, i) {
      var col = side[i % 2];
      if (col.length) col.push(empty);
      blockLines(card).forEach(function (l) { col.push(l); });
    });
    var rows = Math.max(side[0].length, side[1].length), out = [];
    for (var r = 0; r < rows; r++) {
      var line = (side[0][r] || empty).slice();
      if (side[1].length) line = line.concat([''], side[1][r] || empty);
      out.push(line.join('\t'));
    }
    return out.join('\n');
  }

  function copyText(text, btn) {
    var label = btn.textContent;
    function done(m) { btn.textContent = m; setTimeout(function () { btn.textContent = label; }, 1600); }
    function fallback() {
      var ta = document.createElement('textarea');
      ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
      document.body.appendChild(ta); ta.select();
      var ok = false; try { ok = document.execCommand('copy'); } catch (e) {}
      document.body.removeChild(ta); done(ok ? 'Copied' : 'Copy failed');
    }
    try { navigator.clipboard.writeText(text).then(function () { done('Copied'); }, fallback); } catch (e) { fallback(); }
  }

  var all = document.getElementById('copyall');
  all.onclick = function () { copyText(gridText(cards), all); };
  document.addEventListener('click', function (e) {
    var b = e.target.closest('.copy');
    if (b) copyText(gridText([b.closest('[data-card]')]), b);
  });
})();
</script>
</body>
</html>