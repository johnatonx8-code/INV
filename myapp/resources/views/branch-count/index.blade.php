<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Branch Model Count</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap">
<style>
/* Layout: one narrow column. Drop zone on top, branch chips, then the count table with a total row. */
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
body{background:var(--bg);color:var(--ink);font-family:var(--f-body);font-size:15px;line-height:1.45;
  padding-block:28px 48px;padding-left:max(16px,env(safe-area-inset-left,0px));padding-right:max(16px,env(safe-area-inset-right,0px))}
.wrap{max-width:760px;margin:0 auto;display:flex;flex-direction:column;gap:20px}
h1{font-family:var(--f-display);font-weight:700;font-size:34px;letter-spacing:.01em;margin:0;text-wrap:balance}
.sub{color:var(--muted);margin:4px 0 0}
.drop{display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center;padding:26px 16px;border:2px dashed var(--line);
  background:var(--surface);border-radius:6px;transition:border-color .15s,background .15s}
.drop.over{border-color:var(--accent);background:var(--accent-soft)}
.drop b{font-family:var(--f-display);font-size:22px;font-weight:600}
.drop p{margin:0;color:var(--muted);max-width:46ch}
.row{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
button{font:inherit;font-weight:600;cursor:pointer;border-radius:5px;padding:9px 16px;border:1px solid var(--line);background:var(--surface);color:var(--ink)}
button.primary{background:var(--accent);color:var(--accent-ink);border-color:var(--accent)}
button:focus-visible,.chip:focus-visible{outline:3px solid var(--accent);outline-offset:2px}
#paste{position:absolute;opacity:0;width:1px;height:1px;pointer-events:none}
.note{padding:10px 14px;border-radius:5px;background:var(--warn-soft);color:var(--warn);font-weight:500}
.note.ok{background:var(--accent-soft);color:var(--accent)}
.chips{display:flex;flex-wrap:wrap;gap:8px}
.chip{font:inherit;font-weight:600;padding:7px 12px;border-radius:999px;border:1px solid var(--line);background:var(--surface);color:var(--ink);cursor:pointer;display:flex;gap:8px;align-items:baseline}
.chip span{font-family:var(--f-mono);font-size:12px;color:var(--muted);font-weight:400}
.chip[aria-pressed="true"]{background:var(--accent);border-color:var(--accent);color:var(--accent-ink)}
.chip[aria-pressed="true"] span{color:var(--accent-ink)}
.card{background:var(--surface);border:1px solid var(--line);border-radius:6px;overflow:hidden}
.head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;padding:16px 16px 12px}
.name{font-family:var(--f-display);font-weight:700;font-size:30px;letter-spacing:.04em;line-height:1;color:var(--ink);background:transparent;border:0;border-bottom:1px dashed var(--line);padding:0 0 2px;width:min(100%,420px);text-transform:uppercase}
.name:focus-visible{outline:none;border-bottom-color:var(--accent)}
.head h2{font-family:var(--f-display);font-size:30px;letter-spacing:.04em;margin:0;line-height:1}
.head small{color:var(--muted);display:block;margin-top:4px}
.scroll{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-variant-numeric:tabular-nums}
th,td{padding:8px 16px;text-align:left;border-top:1px solid var(--line);white-space:nowrap}
th{font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:600;background:var(--bg)}
td.n,th.n{text-align:right;width:1%}
td.m{font-family:var(--f-mono);font-size:14px}
td.c{color:var(--muted)}
td.t{font-weight:600;font-size:17px}
tfoot td{font-family:var(--f-display);font-size:22px;font-weight:700;background:var(--accent-soft);color:var(--accent);border-top:2px solid var(--accent)}
.brandrow td{background:var(--bg);font-weight:600;font-size:13px;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)}
.tag{font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;padding:2px 8px;border-radius:3px;background:var(--warn-soft);color:var(--warn)}
</style>
</head>
<body>
<div class="wrap">
  <header>
    <h1>Branch Model Count</h1>
    <p class="sub">Drop in an Ending Inventory file. Units are counted per model, with colors merged, for each branch.</p>
  </header>

 <form class="drop" id="drop" method="POST" action="{{ route('branch-count.store', [], false) }}" enctype="multipart/form-data">
    @csrf
    <b>Paste or drop the .xlsx here</b>
    <p>Press Ctrl+V after copying the file, drag it onto this box, or pick it from your computer. It is counted and then discarded. Nothing is saved.</p>
    <div class="row"><button class="primary" id="pick" type="button">Choose file</button></div>
    <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" hidden>
  </form>

  @if ($errors->any())
    <div class="note">{{ $errors->first() }}</div>
  @endif
  @if ($error)
    <div class="note">{{ $error }}</div>
  @endif

  @if ($result)
    @php $list = $result['list']; $sum = $result['total']; @endphp
    <div class="note ok">Counted {{ $sum }} units in {{ count($list) }} models.</div>

    <section class="card">
      <div class="head">
        <div>
          <input id="title" class="name" type="text" value="{{ $result['name'] }}" aria-label="Branch name" spellcheck="false">
          <small>{{ $result['asOf'] ? $result['asOf'].' · ' : '' }}{{ count($list) }} models</small>
        </div>
        <div class="row"><button type="button" class="copy">Copy table</button></div>
      </div>
      <div class="scroll">
        <table>
          <thead><tr>
  <th class="n">#</th>
  <th class="sort" data-col="1" aria-sort="none" tabindex="0">Brand</th>
  <th class="sort" data-col="2" aria-sort="none" tabindex="0">Model</th>
  <th class="sort n" data-col="3" data-type="num" aria-sort="none" tabindex="0">Total</th>
</tr></thead>
          <tbody>
            @foreach ($list as $n => [$brand, $model, $count])
              <tr>
                <td class="n c">{{ $n + 1 }}</td>
                <td>{{ $brand }}</td>
                <td class="m">{{ $model }}</td>
                <td class="n t">{{ $count }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot><tr><td colspan="3">Total</td><td class="n">{{ $sum }}</td></tr></tfoot>
        </table>
      </div>
      <textarea class="plain" hidden>
#	Brand	Model	Total
@foreach ($list as $n => [$brand, $model, $count]){{ $n + 1 }}	{{ $brand }}	{{ $model }}	{{ $count }}
@endforeach
Total:			{{ $sum }}</textarea>
    </section>
  @endif
</div>

<script>
document.querySelectorAll('th.sort').forEach(function (th) {
  function sortRows() {
    var col = +th.dataset.col, num = th.dataset.type === 'num';
    var dir = th.getAttribute('aria-sort') === 'ascending' ? -1 : 1;
    document.querySelectorAll('th.sort').forEach(function (o) { o.setAttribute('aria-sort', 'none'); });
    th.setAttribute('aria-sort', dir === 1 ? 'ascending' : 'descending');
    var tbody = th.closest('table').tBodies[0];
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
  th.addEventListener('click', sortRows);
  th.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); sortRows(); } });
});
</script>
</body>
</html>
