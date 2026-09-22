<?php
require_once 'db.php';
$default = getDefaultDistrict($conn);
$districtId = isset($_GET['district_id']) ? (int)$_GET['district_id'] : (int)$default['id'];
$district = getDistrict($conn, $districtId) ?: $default;
$states = getStates($conn);
$districts = getDistricts($conn);
$stats = [
 'crops' => (int)scalar($conn,"SELECT COUNT(*) FROM crops"),
 'varieties' => (int)scalar($conn,"SELECT COUNT(*) FROM varieties"),
 'districts' => (int)scalar($conn,"SELECT COUNT(*) FROM districts"),
 'genomic' => (int)scalar($conn,"SELECT COUNT(*) FROM genomic_profiles")
];
$top = one($conn,"SELECT r.*,v.name variety_name,c.name crop_name,v.tagline FROM recommendation_scores r JOIN varieties v ON v.id=r.variety_id JOIN crops c ON c.id=v.crop_id WHERE r.district_id=? ORDER BY r.overall_score DESC LIMIT 1",'i',$district['id']);
$weather = one($conn,"SELECT * FROM weather_current WHERE district_id=?",'i',$district['id']);
$normal = one($conn,"SELECT * FROM climate_normals WHERE district_id=?",'i',$district['id']);
$forecast = rows($conn,"SELECT f.crop_year,f.predicted_yield_t_ha,f.yield_advantage_pct,f.confidence_pct,f.model_name,v.name variety_name FROM yield_forecasts f JOIN varieties v ON v.id=f.variety_id WHERE f.district_id=? ORDER BY f.crop_year, f.predicted_yield_t_ha DESC LIMIT 8",'i',$district['id']);
page_header('Dashboard','index');
?>
<section class="location-bar"><div class="location-left"><div class="location-icon">📍</div><div><span>Selected Location</span><strong><?=h($district['name'])?>, <?=h($district['state_name'])?></strong></div></div><form method="get"><select name="district_id" onchange="this.form.submit()" class="gc-select-small"><?php foreach($districts as $d):?><option value="<?=$d['id']?>" <?=$d['id']==$district['id']?'selected':''?>><?=h($d['name'])?>, <?=h($d['state_name'])?></option><?php endforeach;?></select></form></section>
<section class="welcome"><div><p class="welcome-tag">PRECISION AGRICULTURE INTELLIGENCE</p><h2>Make smarter crop decisions with <span>GeneCrop AI</span></h2><p class="welcome-text">Analyze soil, climate, historical performance, genomic traits and future yield forecasts for <?=h($district['name'])?>.</p><a class="primary-button" href="recommendation.php?district_id=<?=$district['id']?>">🌾 View Recommendations</a></div><div class="welcome-graphic"><div class="circle circle-one"></div><div class="circle circle-two"></div><div class="plant">🌱</div></div></section>
<section class="stats-grid">
<?php $cards=[['🌾','Crops',$stats['crops'],'Crop types'],['🧬','Varieties',$stats['varieties'],'Registered varieties'],['📍','Districts',$stats['districts'],'Analysis locations'],['🔬','Genomic Profiles',$stats['genomic'],'Variety profiles']]; foreach($cards as $c):?><div class="stat-card"><div class="stat-top"><div class="stat-icon"><?=$c[0]?></div><span class="stat-title"><?=$c[1]?></span></div><div class="stat-value"><?=$c[2]?></div><div class="stat-footer"><?=$c[3]?></div></div><?php endforeach;?></section>
<section class="dashboard-grid">
<div class="panel"><div class="panel-header"><div><p class="panel-label">TOP RECOMMENDATION</p><h3><?=h($top['variety_name'] ?? 'No recommendation')?></h3></div><span class="recommend-score"><?=number_format((float)($top['overall_score']??0),1)?>%</span></div><?php if($top):?><p class="muted"><?=h($top['crop_name'])?> • <?=h($top['tagline'])?></p><div class="property-list"><div class="property"><span>Expected Yield</span><strong><?=number_format($top['expected_yield_t_ha'],2)?> t/ha</strong></div><div class="property"><span>Soil Score</span><strong><?=number_format($top['soil_score'],1)?>%</strong></div><div class="property"><span>Climate Score</span><strong><?=number_format($top['climate_score'],1)?>%</strong></div><div class="property"><span>Genomic Score</span><strong><?=number_format($top['genomic_score'],1)?>%</strong></div></div><p class="insight-text">💡 <?=h($top['insight_text'])?></p><?php endif;?></div>
<div class="panel"><div class="panel-header"><div><p class="panel-label">CURRENT CONDITIONS</p><h3><?=h($district['name'])?> Weather</h3></div><span class="weather-badge">🌤️</span></div><div class="temperature"><strong><?=number_format((float)($weather['temperature_c']??0),1)?>°</strong><span>C</span></div><p class="muted"><?=h($weather['condition_text']??'No weather data')?></p><div class="property-list"><div class="property"><span>Humidity</span><strong><?=h($weather['humidity_pct']??'-')?>%</strong></div><div class="property"><span>Rainfall</span><strong><?=h($weather['rainfall_mm']??'-')?> mm</strong></div><div class="property"><span>Annual Rainfall</span><strong><?=h($normal['annual_rainfall_mm']??'-')?> mm</strong></div></div></div>
</section>
<section class="yield-chart-card"><div class="chart-title"><div><p class="panel-label">FUTURE YIELD OUTLOOK</p><h3>Forecast performance</h3></div></div><div class="forecast-mini-grid"><?php foreach($forecast as $f):?><div class="forecast-mini"><span><?=$f['crop_year']?></span><strong><?=number_format($f['predicted_yield_t_ha'],2)?> t/ha</strong><small>+<?=number_format($f['yield_advantage_pct'],1)?>% advantage • <?=number_format($f['confidence_pct'])?>% confidence</small></div><?php endforeach;?></div></section>
<?php page_footer(); ?>
