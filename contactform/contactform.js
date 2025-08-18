jQuery(document).ready(function($) {
  "use strict";

  // Contact Form Submission
  $('form.contactForm').submit(async function(e) {
    e.preventDefault(); // Prevent default form submission

    var form = $(this);
    var f = form.find('.form-group');
    var ferror = false;

    // Validate inputs
    f.children('input, textarea').each(function() {
      var i = $(this); // current input
      var rule = i.attr('data-rule');

      if (rule !== undefined) {
        var ierror = false; // error flag for current input
        var pos = rule.indexOf(':', 0);
        if (pos >= 0) {
          var exp = rule.substr(pos + 1, rule.length);
          rule = rule.substr(0, pos);
        } else {
          rule = rule.substr(pos + 1, rule.length);
        }

        switch (rule) {
          case 'required':
            if (i.val() === '') {
              ferror = ierror = true;
            }
            break;

          case 'minlen':
            if (i.val().length < parseInt(exp)) {
              ferror = ierror = true;
            }
            break;

          case 'email':
            var emailExp = /^[^\s()<>@,;:\/]+@\w[\w\.-]+\.[a-z]{2,}$/i;
            if (!emailExp.test(i.val())) {
              ferror = ierror = true;
            }
            break;
        }
        i.next('.validation').html((ierror ? (i.attr('data-msg') !== undefined ? i.attr('data-msg') : 'wrong Input') : '')).show('blind');
      }
    });

    if (ferror) return false;

    // Prepare form data
    var formData = new FormData(this);

    try {
      // Send data using fetch
      const response = await fetch('contactform/contactFrom.php', {
        method: 'POST',
        body: formData,
      });

      const result = await response.text(); // Get server response
      var responseMessage = $('#responseMessage');

      // Clear previous alert classes
      responseMessage.removeClass('alert-success alert-danger');

      if (response.ok) {
        responseMessage.addClass('alert alert-success');
        responseMessage.text('¡Correo enviado exitosamente!');
        form[0].reset(); // Reset the form
      } else {
        responseMessage.addClass('alert alert-danger');
        responseMessage.text(`Error: ${result}`);
      }

      responseMessage.show();

      // Hide the message after 10 seconds
      setTimeout(() => {
        responseMessage.fadeOut();
      }, 10000);
    } catch (error) {
      var responseMessage = $('#responseMessage');
      responseMessage.removeClass('alert-success').addClass('alert alert-danger');
      responseMessage.text('Error al enviar el formulario. Intente nuevamente.');
      responseMessage.show();

      // Hide the message after 10 seconds
      setTimeout(() => {
        responseMessage.fadeOut();
      }, 10000);

      console.error('Error al enviar el formulario:', error);
    }
  });
});
