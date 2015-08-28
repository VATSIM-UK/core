<?php 

interface iTimelineEntry {
    public function getDisplayValueAttribute();
    public function timelineEntriesOwner();
    public function timelineEntriesExtra();
    public function getTimelineEntriesRecentAttribute();
}