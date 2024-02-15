jQuery(document).ready(function ($) {


    // Declare a global variable to store the selected car model value
    var selectedCarMake = '';
    var selectedCarYear = '';
    var selectedCarModel = '';

    // Assuming your model select field has the ID 'car_model_select'
    $('#car-make').on('change', function () {
        selectedCarMake = $(this).val();
        $('#car-models').empty();
        $('#car-models').append('<option>Select Car Model</option>');
        $('#car-models').prop("disabled", true);
        $('#car-years').empty();
        $('#car-years').append('<option>Select Car Year</option>');
        $('#car-years').prop("disabled", true);
        $('#car-area-select').addClass('hidden');
        $('#car-area-select-container').addClass('hidden');
        $('#car-area-select').empty();
        $("#car-area").val('');


        // Call your other functions or perform additional actions if needed
        // For example, trigger an AJAX request to get car models based on the selected car model
        getCarModels(selectedCarMake);
    });

    // Function to get car models based on the selected car model
    function getCarModels(selectedCarMake) {
        $('#car-models').prop("disabled", false);
        var get_car_nonce = wrw_ajax_object.carNonce;
        // Perform AJAX request to get car models using the selected model
        $.ajax({
            type: "POST",
            url: my_ajaxurl,
            data: {
                action: 'wrw_return_car_models',
                car_make: selectedCarMake,
                _wpnonce: get_car_nonce
            },
            success: function (data) {

                populateCarModelsDropdown(data);

                // Update your UI or perform actions with the retrieved data
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            },
            beforeSend: function () {
                // Add any pre-AJAX call actions if needed
            },
            complete: function () {
                // Add any post-AJAX call actions if needed
            }
        });
    }

    // Function to populate car models dropdown based on API response
    function populateCarModelsDropdown(modelsData) {
        // Assuming your car models select field has the ID 'car_models_dropdown'

        var carModelsDropdown = $("#car-models");


        // Clear existing options
        carModelsDropdown.empty();
        //carModelsDropdown.append('<option value="volvo">Volvo</option>');
        carModelsDropdown.append(modelsData);

        // Populate options based on API response
        // modelsData.forEach( model => {
        //     // Use model.id as the option value and model.model as the option text
        //     //carModelsDropdown.append('<option value="' + model.id + '">' + model.model + '</option>');
        //     carModelsDropdown.append(model);
        //     //carModelsDropdown.append('<option value="volvo">Volvo</option>');
        // });

        // modelsData.forEach(function(model) {
        //     carModelsDropdown.append($('<option>', {
        //         value: model.id,   // Assuming you want to use the ID as the option value
        //         text: model.model
        //     }));
        // });
        // let data = '';
        // modelsData.forEach(function(model) {
        //     let car_model = model.model
        //     data += '<option value="' + car_model + '">' + car_model + '</option>';
        // });

        // alert(data)

        const select = carModelsDropdown.get(0);
        const fragment = document.createDocumentFragment(); // Improve performance for large datasets

        //  modelsData.forEach(model => {
        // const option = document.createElement('option');
        // option.value = model.model;
        // option.textContent = model.model;
        // fragment.appendChild(option);
        // });

        // select.appendChild(fragment); // Append options in bulk

        // modelsData.forEach(model => {
        // const option = document.createElement('option');
        // option.value = model.model;
        // option.textContent = model.model;
        // option.innerHTML = `<option value="${model.model}">${model.model}</option>`; // Shorthand syntax
        // fragment.appendChild(option);
        // });
        // select.appendChild(fragment);
    }







    // Assuming your model select field has the ID 'car_model_select'
    $('#car-models').on('change', function () {
        selectedCarModel = $(this).val();

        // Call your other functions or perform additional actions if needed
        // For example, trigger an AJAX request to get car models based on the selected car model
        getCarYears(selectedCarModel);
    });

    // Function to get car models based on the selected car model
    function getCarYears(selectedCarModel) {
        var get_car_year_nonce = wrw_ajax_object.yearNonce;
        $('#car-years').prop("disabled", false);
        // Perform AJAX request to get car models using the selected model
        $.ajax({
            type: "POST",
            url: my_ajaxurl,
            data: {
                action: 'wrw_return_car_years',
                car_make: selectedCarMake,
                car_model: selectedCarModel,
                _wpnonce: get_car_year_nonce
            },
            success: function (data) {

                populateCarYearsDropdown(data);

                // Update your UI or perform actions with the retrieved data
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            },
            beforeSend: function () {
                // Add any pre-AJAX call actions if needed
            },
            complete: function () {
                // Add any post-AJAX call actions if needed
            }
        });
    }

    // Function to populate car models dropdown based on API response
    function populateCarYearsDropdown(yearsData) {
        // Assuming your car models select field has the ID 'car_models_dropdown'
        var carYearsDropdown = $("#car-years");


        // Clear existing options
        carYearsDropdown.empty();
        carYearsDropdown.append(yearsData);


    }




    // Event listener for the model select
    // Assuming your model select field has the ID 'car_model_select'
    $('#car-years').on('change', function () {
        selectedCarYear = $(this).val();
        // Call your other functions or perform additional actions if needed
        // For example, trigger an AJAX request to get car models based on the selected car model
        getCarAreaOrRoofType(selectedCarYear);
    });
    $('#car-area-select').on('change', function () {
        jQuery('#car-area').val($(this).val());

    });


    // Function to get car models based on the selected car model
    function getCarAreaOrRoofType(selectedCarYear) {
        var get_car_area_nonce = wrw_ajax_object.areaNonce;
        // $('#car-years').prop("disabled", false);
        // Perform AJAX request to get car models using the selected model
        $.ajax({
            type: "POST",
            url: my_ajaxurl,
            data: {
                action: 'wrw_get_car_area',
                car_make: selectedCarMake,
                car_model: selectedCarModel,
                car_year: selectedCarYear,
                _wpnonce: get_car_area_nonce
            },
            success: function (data) {

                var data_array = data.split(',');

                if (data_array[0] !== "0") {
                    // roof = yes
                    jQuery("#car-area-select").removeClass("hidden");
                    jQuery("#car-area-select-container").removeClass("hidden");
                    jQuery("#car-area-select").append(data);

                    // Set value to #wrw-calculator based on selected roof type


                    //Add makeSelected value to #car_make_input input:text
                    // jQuery("#car_roof_input input:text").val("Yes");

                } else if (data_array[0] == "0") {
                    //roof = no
                    jQuery("#car-area-select").addClass("hidden");
                    jQuery("#car-area-select-container").addClass("hidden");
                    let calSquareRootOfArea = Number(Math.sqrt(data_array[1]) * 12);
                    //jQuery("<p class=\"total-square-footage\"><strong>Total Sq. Ft.:</strong> " + calSquareRootOfArea.toPrecision(4) + "</p>").insertAfter("select#car_roof_select");
                    //jQuery("#car-area").val(calSquareRootOfArea);

                    // Set value to #wrw-calculator when there is no roof
                    jQuery('#car-area').val(calSquareRootOfArea.toPrecision(4));

                };

                // Update your UI or perform actions with the retrieved data
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            },
            beforeSend: function () {
                // Add any pre-AJAX call actions if needed
            },
            complete: function () {
                // Add any post-AJAX call actions if needed
            }
        });
    }

});
