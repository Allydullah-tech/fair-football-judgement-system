const forms = document.querySelectorAll("form");

forms.forEach(form => {

    form.addEventListener("submit", function(event) {

        const inputs = form.querySelectorAll("input");

        for (let input of inputs) {

            if (input.value.trim() === "") {

                alert("Please fill all fields");

                event.preventDefault();

                return;
            }

        }

    });

});