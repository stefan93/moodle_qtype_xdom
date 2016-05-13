<?php
require_once($CFG->libdir . '/pagelib.php');
global $PAGE;

$PAGE->requires->css("/lib/jquery/ui-1.11.4/jquery-ui.css");
$PAGE->requires->js_call_amd('qtype_xdom/xdommodule','edit_form');
$prva_strana="prva strana";
$druga_strana="druga strana";
$treca_strana="treca strana";
$window=<<< EOT
<div id="tabs">
  <ul>
    <li><a href="#fragment-1">One</a></li>
    <li><a href="#fragment-2">Two</a></li>
    <li><a href="#fragment-3">Three</a></li>
  </ul>
  <div id="fragment-1">
    $prva_strana
  </div>
  <div id="fragment-2">
    $druga_strana
  </div>
  <div id="fragment-3">
    $treca_strana
  </div>
</div>
EOT;
$settings->add(new admin_setting_heading("ime","<p>Naslov</p>",$window));
