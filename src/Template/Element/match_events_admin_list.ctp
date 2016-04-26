<?php
    foreach($matchEventsForAdminsEventList as $event){
        /*
        SELECT mem.*, mp.club_id, p.name player_name, p.surname player_surname, me.name event_name
        FROM match_events_matches mem
        JOIN matches_players mp ON (mp.player_id = mem.player_id AND mp.match_id = :match_id)
        JOIN players p ON (p.id = mem.player_id)
        JOIN match_events me ON (me.id = mem. event_id)
        WHERE mem.match_id = :match_id
        ORDER BY minute ASC, id ASC
        */
?>
    <div class="row">
        <div class="col-md-12">
            <?php
                echo $this->Html->link($this->Html->image(
                        "delete.icon.png", ["class" => "delete_icon_small"]),
                        "http://www.pino.webekacko.com",
                        ['escapeTitle' => false, 'onclick' => 'deleteEventInMatch('.$event['id'].'); return false;']
                    );
                echo " ".$event["minute"].". minúta";
                echo " - ".$event["event_name"];
                echo ' - <span class="surname">'.$event["player_surname"]."</span> ".$event["player_name"];
            ?>
        </div>
    </div>
<?php
    }
?>

<div class="row">
    <div class="col-md-12 input_error" id="delete_event_in_match_ajax_error"></div>
    <div class="col-md-12 input_error">
        <?php
            if($this->request->session()->check("deleteEventInMatch.deleteError")){
                echo $this->request->session()->consume("deleteEventInMatch.deleteError");
            }
        ?>
    </div>
</div>

<script type="text/javascript">
    function deleteEventInMatch(event_id){
        $.ajax({
            url: "<?= $this->Url->build(["controller" => "Matches", "action" => "hun_delete_event_in_match", $matchInfo['id'] ]); ?>" + "/" + event_id,
            method : "POST",
            error: function(jqXHR, status, error){
                $('#delete_event_in_match_ajax_error').html("chyba pri AJAXovom volaní pri mazaní udalosti. Skús to znova a ak problém pretrváva ozvy sa Šimonovi.");
            },
            success: function(result){
                $("#content_container").html(result);
            }
        });
    }
</script>

<?php
    echo $this->request->session()->delete("deleteEventInMatch");
?>