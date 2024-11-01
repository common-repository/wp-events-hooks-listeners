(function () {
    var actionBasedConditionsKeys = {
        "someone_create_post": {
            "fields": [{
                "username": {
                    "ajax_actions": "load_users",
                    "operators": ["="]
                }
            }]
        }
    };

    /*jQuery(document).ready(function () {
        var maxField = 10; //Input fields increment limitation
        var addButton = jQuery('.add_button'); //Add button selector
        var wrapper = jQuery('#conditions_builder'); //Input field wrapper
        var fieldHTML = '<div class="row condition-1">\n' +
            '                                        <div class="col-4">\n' +
            '                                            <select class="form-control" name="condition_key[]">\n' +
            '                                                <option value="" selected="selected">Condition</option>\n' +
            '                                            </select>\n' +
            '                                        </div>\n' +
            '                                        <div class="col-2">\n' +
            '                                            <select class="form-control" name="condition_operator[]">\n' +
            '                                                <option value="" selected="selected">Select operator</option>\n' +
            '                                                <option value="equals">=</option>\n' +
            '                                            </select>\n' +
            '                                        </div>\n' +
            '                                        <div class="col-4">\n' +
            '                                            <select class="form-control" name="condition_value[]">\n' +
            '                                                <option value="" selected="selected">Select users</option>\n' +
            '                                                <option value="shaharia.azam">shaharia.azam</option>\n' +
            '                                            </select>\n' +
            '                                        </div>\n' +
            '                                        <div class="col-2">\n' +
            '                                            <a href="javascript:void(0);" class="btn btn-danger btn-sm remove_button" onclick="jQuery(\'#conditions_builder\').append(jQuery(\'.condition-1\').clone().find(\'.condition-1\'))">-</a>\n' +
            '                                        </div>\n' +
            '                                    </div>'; //New input field html
        var x = 1; //Initial field counter is 1

        //Once add button is clicked
        jQuery(addButton).click(function(){
            //Check maximum number of input fields
            if(x < maxField){
                x++; //Increment field counter
                jQuery(wrapper).append(fieldHTML); //Add field html
            }
        });

        //Once remove button is clicked
        jQuery(wrapper).on('click', '.remove_button', function(e){
            e.preventDefault();
            console.log(jQuery(this).parent('div').parent().remove());
            jQuery(this).parent('div').remove(); //Remove field html
            x--; //Decrement field counter
        });

        jQuery("#add_action_btn").on("click", function (e) {
            e.preventDefault();

            var key = jQuery(("select[name='condition_key\\[\\]']")).map(function(idx, elem) {
                return jQuery(elem).val();
            }).get();

            var operator = jQuery(("select[name='condition_operator\\[\\]']")).map(function(idx, elem) {
                return jQuery(elem).val();
            }).get();

            var value = jQuery(("select[name='condition_value\\[\\]']")).map(function(idx, elem) {
                return jQuery(elem).val();
            }).get();

            var conditions = [];
            for(i=0; i<=key.length; i++){
                conditions.push({key: key[i], operator: operator[i], value: value[i]});
            }

            console.log(conditions);
        })
    });*/

    /*jQuery("#event_type_when").on("change", function (e) {
        var eventType = jQuery(this).val();
        if(eventType !== undefined){
            jQuery("#conditions_builder").show();

            /!*for (var k in actionBasedConditionsKeys[eventType]["fields"]){
                if (actionBasedConditionsKeys[eventType]["fields"].hasOwnProperty(k)) {
                    alert("Key is " + k + ", value is " + actionBasedConditionsKeys[eventType]["fields"][k]);
                }
            }*!/

            actionBasedConditionsKeys[eventType]["fields"].forEach(function (v, k) {
                Object.keys(v).forEach(function (value) {
                    jQuery(("select[name='condition_key\\[\\]']")).append("<option value='" + Object.keys(v)[0] +"'>"+Object.keys(v)[0]+"</option>");
                    console.log(actionBasedConditionsKeys[eventType]["fields"].value);
                    actionBasedConditionsKeys[eventType]["fields"][value].operators.forEach(function (e) {
                        jQuery(("select[name='condition_operator\\[\\]']")).append("<option value='" + e +"'>"+e+"</option>");
                    })
                })
                //jQuery(("select[name='condition_key\\[\\]']")).append("<option value='" + Object.keys(v)[0] +"'>"+Object.keys(v)[0]+"</option>");

                //jQuery(("select[name='condition_operator\\[\\]']")).append("<option value='" + Object.keys(v)[0]["operators"] +"'>"+Object.keys(v)[0]+"</option>")
            })
        }
    })*/

    jQuery(document).ready(function () {
        jQuery("#event_actions").hide();
    });

    jQuery("#event_type_when").on("change", function (e) {
        var eventType = jQuery(this).val();

        if(eventType.length < 2){
            jQuery("#event_actions").hide();
        }else{
            jQuery("#event_actions").show();
        }

        var data = {
            'action': 'waoe_get_actions_webhooks',
            'event': eventType
        };

        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajaxurl, data, function (response) {
            //console.log(response);
            console.log(response);
            if(response.hasOwnProperty("data") && response.data.hasOwnProperty("event")){
                jQuery("#send_to_webhook").val(response.data.webhook_url);
            }
        });
    });

    jQuery("#add_action_btn").on("click", function () {
        var data = {
            'action': 'waoe_save_actions_webhooks',
            'event': jQuery("#event_type_when").val(),
            'webhook_url': jQuery("#send_to_webhook").val()
        };

        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajaxurl, data, function (response) {
            if(response.data.hasOwnProperty("success") && response.data.success === false){
                Swal.fire(
                    'Ooops!',
                    'Failed to save action. ' + response.data.message,
                    'error'
                )
            }else{
                Swal.fire(
                    'Good job!',
                    'Action has been saved successfully!',
                    'success'
                )
            }
        });
    })
})();