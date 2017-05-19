$(document).ready(function() {
    // $("table.dataTable > thead > tr  input[type=checkbox]").bind("click",(function() {
    //     // alert('asdad');
    //     console.log($(this).attr("checked"));
    //     var bool;
    //     if ($(this).attr("checked") == "checked") {
    //         bool = true;
    //     } else {
    //         bool = false;
    //     }
    //     // selectAllCheckBox(bool);
    // });
    $("table.dataTable > thead > tr  input[type=checkbox]").bind("click", function() {
        selectAllCheckBox(this.checked);
    });
});





function selectAllCheckBox(bool) {
    $("table.dataTable > tbody > tr  input[type=checkbox]").attr("checked", bool).prop("checked", bool);
    $("table.dataTable tbody tr td input[type=checkbox]:not(input[disabled])").attr("checked", bool).prop("checked", bool);
}

function getCheckboxValue() {
    var adIds = "";
    $("table.dataTable > tbody > tr  input[type=checkbox]:checked").each(function(i) {
        if (0 == i) {
            adIds = $(this).val();
        } else {
            adIds += ("," + $(this).val());
        }
    });
    return adIds;
}
