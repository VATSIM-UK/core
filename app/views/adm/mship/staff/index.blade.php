<?php

function outputPosition($position, $all_positions)
{
    $indent = Models\Staff\Position::totalParents($position, $all_positions) * 50;

    $output_string = '';
    if ($position->type === 'D') {
        $output_string .= "<div style='padding-left: {$indent}px; font-weight: bold;'>{$position->name}</div>";
    } elseif ($position->type === 'P') {
        $output_string .= "<div id='{$position->id}' onclick='addStaff(this)' style='padding-left: {$indent}px; cursor: pointer;'>{$position->name} - ";
        if ($position->filledBy->isEmpty()) {
            $output_string .= "<span class='label label-danger'>Vacant</span>";
        } else {
            foreach ($position->filledBy as $staff) {
                $output_string .= $staff->name_first . ' ' . $staff->name_last;
            }
        }
        $output_string .= '</div>';
    }

    return $output_string;

}

?>

@extends('adm.layout')

@section('content')

@foreach ($ordered_positions as $position)
    {{ outputPosition($position, $positions) }}
@endforeach

<script type="text/javascript">
function addStaff(node) {
    alert('Are you sure you want to remove node ' + node.id + '?');
}
</script>

@stop
