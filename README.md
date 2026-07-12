<h1>DC Omnibus</h1>

<h2>About</h2>

<p>
  <strong>DC Omnibus</strong> helps OpenCart 3 stores comply with the EU Omnibus directive (Polish: ustawa o informowaniu o obniżce ceny produktu).
  When a product is on sale, customers must see not only the current promotional price, but also the
  <strong>lowest price from the previous 30 days</strong>.
</p>

<p>
  The module logs promotional prices when products are saved in the admin panel, stores history in lightweight JSON cache files,
  and injects a configurable price panel on the product page via JavaScript.
</p>

<p>
  Author: <strong>Paweł Nosko</strong> · Company: <strong>Design Cart</strong> · Module code: <code>dc_omnibus</code>
</p>

<h2>Key features</h2>

<ul>
  <li>Automatic logging of promotional prices on product save (OpenCart events)</li>
  <li><strong>Sync active promotions</strong> — one-click backfill for all existing promotions in the store</li>
  <li>Per-product cache files in <code>storage/dc_omnibus_cache/{product_id}.txt</code></li>
  <li>30-day rolling window for lowest price calculation</li>
  <li>Multi-currency and customer group support</li>
  <li>Option prices excluded — base promotional price only</li>
  <li>Configurable panel appearance (colors, typography, background)</li>
  <li>Per-language labels (default: “Lowest price in 30 days”)</li>
  <li>CSS selector-based placement on the product page</li>
  <li>Display always or promotion-only mode</li>
  <li>Admin file list and price history viewer</li>
  <li>No extra database tables — file-based storage</li>
</ul>

<h2>Requirements</h2>

<ul>
  <li>OpenCart 3.0.x or 3.5.x</li>
  <li>PHP with write access to <code>storage/</code></li>
  <li>OCMOD enabled</li>
  <li>jQuery (included in OpenCart 3)</li>
</ul>

<h2>Installation</h2>

<ol>
  <li>Upload the contents of the <code>upload/</code> folder to your OpenCart root directory.</li>
  <li>Install <code>install.xml</code> via <strong>Extensions → Extension Installer</strong>.</li>
  <li>Refresh modifications at <strong>Extensions → Modifications</strong>.</li>
  <li>Install and enable the module at <strong>Extensions → Extensions → Modules → DC Omnibus</strong>.</li>
  <li>Go to the <strong>Files</strong> tab and click <strong>Sync active promotions</strong> to backfill cache for existing promotions.</li>
  <li>Adjust CSS selectors in the <strong>Frontend</strong> tab to match your theme.</li>
</ol>

<h2>Sync active promotions</h2>

<p>
  After installation, existing promotions are <strong>not</strong> logged automatically until each product is saved again.
  Use the <strong>Sync active promotions</strong> button in the admin panel (Files tab) to create cache files
  for all currently active special prices in one operation. This is essential for stores that already had promotions before installing the module.
</p>

<h2>How it works</h2>

<h3>Admin (price logging)</h3>
<p>
  On product add/edit, OpenCart events trigger <code>DcOmnibusPriceLogger</code>, which appends promotional prices to the product cache file.
  The front end never writes to cache — read only.
</p>

<h3>Storefront (display)</h3>
<p>
  OCMOD hooks inject configuration into the product page. PHP reads the cache file, calculates the lowest price from the last 30 days,
  and <code>dc_omnibus.js</code> inserts a styled panel next to a configurable CSS selector.
</p>

<h2>Default selectors</h2>

<ul>
  <li>Special price: <code>.product-price-special</code></li>
  <li>Panel insertion: <code>.product-rating</code> (after)</li>
</ul>

<p>Adjust these in the module settings for custom themes. Design Cart theme is supported out of the box.</p>

<h2>Cache file format</h2>

<p>Each entry in <code>storage/dc_omnibus_cache/{product_id}.txt</code>:</p>

<pre>{
  "ts": "2026-07-01 11:33:37",
  "price": 1367.76,
  "currency": "PLN",
  "group": 1
}</pre>

<h2>Learn more</h2>

<ul>
  <li>Full article (Polish): <a href="https://www.designcart.pl/aktualnosci/116-czym-jest-dyrektywa-omnibus.html">What is the Omnibus directive?</a></li>
  <li>Project page: <a href="https://www.designcart.pl">designcart.pl</a></li>
  <li>Author: <a href="https://www.designcart.pl/pawel-nosko.html">Paweł Nosko</a></li>
  <li>Contact: <a href="mailto:info@designcart.pl">info@designcart.pl</a></li>
</ul>

<h2>License &amp; version</h2>

<p>Version: <strong>1.0.3</strong></p>

<p>© Design Cart · Paweł Nosko</p>
