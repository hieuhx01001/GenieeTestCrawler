<?php
/**
 * Created by PhpStorm.
 * User: hieuhoang
 * Date: 6/2/16
 * Time: 5:43 PM
 */

include "Crawler.php";

$crawler = new Crawler();
$crawler_data = [];
if($crawler->loginAndSaveCookie()){
    $crawler_data = $crawler->getScrapData();
}

$total_count_by_date = array_count_values($crawler->getDataArray());

?>
<?php $tmp_breakpoint_count = 0; ?>
<?php foreach ((array)$total_count_by_date as $key_tcbd => $value_tcbd): ?>
<h4><?php echo "Date: " . $key_tcbd ?></h4>
<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <tr>
            <th>No</th>
            <th>partner</th>
            <th>placement_id</th>
            <th>impression</th>
            <th>click</th>
            <th>revenue</th>
            <th>date</th>
        </tr>
        <?php for ($i_cd = $tmp_breakpoint_count; $i_cd < $tmp_breakpoint_count + $value_tcbd; $i_cd++): ?>

            <tr>
                <td><?php echo $i_cd; ?></td>
                <td><?php echo $crawler_data[$i_cd]['partner']; ?></td>
                <td><?php echo $crawler_data[$i_cd]['placement_id']; ?></td>
                <td><?php echo $crawler_data[$i_cd]['impression']; ?></td>
                <td><?php echo $crawler_data[$i_cd]['click']; ?></td>
                <td><?php echo $crawler_data[$i_cd]['revenue']; ?></td>
                <td><?php echo $crawler_data[$i_cd]['date']; ?></td>
            </tr>

        <?php endfor; ?>
        <?php $tmp_breakpoint_count = $value_tcbd; ?>
    </table>
    <?php endforeach; ?>
</div>
