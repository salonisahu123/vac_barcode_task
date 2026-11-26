//////////////// Create & Edit page ////////////////////////
$(document).ready(function(){

    function checkTicketType()
    {
        let type = $('#ticket_type').val();

        if(type == 'location')
        {
            $('#locationBox').removeClass('d-none');
            $('#employeeBox').addClass('d-none');
        }
        else if(type == 'individual')
        {
            $('#employeeBox').removeClass('d-none');
            $('#locationBox').addClass('d-none');
            $('#location_id').val('');
        }
        else
        {
            $('#locationBox').addClass('d-none');
            $('#employeeBox').addClass('d-none');
            $('#location_id').val('');
        }
    }

    checkTicketType();

    $('#ticket_type').change(function(){
        checkTicketType();
    });

    if ($.fn.select2) {
        $('.select2').select2({
            width: '100%'
        });
    }

});
