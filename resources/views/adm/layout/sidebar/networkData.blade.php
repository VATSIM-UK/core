@if($_account->hasChildPermission("adm/networkdata"))
    @include("network-data.admin._sidebar")
@endif