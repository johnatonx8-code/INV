<section class="card" data-card>
  <div class="head">
    <div>
      <input class="name" type="text" value="{{ $b['name'] }}" aria-label="Branch name" spellcheck="false">
      <small>{{ $b['asOf'] ? $b['asOf'].' · ' : '' }}{{ count($b['list']) }} models</small>
    </div>
    <div class="row tools">
      <button type="button" class="mini move" data-dir="-1" aria-label="Move earlier" title="Move earlier">&larr;</button>
      <button type="button" class="mini move" data-dir="1" aria-label="Move later" title="Move later">&rarr;</button>
      <button type="button" class="mini remove" aria-label="Remove this branch" title="Remove this branch">&times;</button>
      <button type="button" class="copy">Copy</button>
    </div>
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
        @foreach ($b['list'] as $n => [$brand, $model, $count])
          <tr>
            <td class="n c">{{ $n + 1 }}</td>
            <td>{{ $brand }}</td>
            <td class="m">{{ $model }}</td>
            <td class="n t">{{ $count }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot><tr><td colspan="3">Total</td><td class="n tot">{{ $b['total'] }}</td></tr></tfoot>
    </table>
  </div>
</section>
