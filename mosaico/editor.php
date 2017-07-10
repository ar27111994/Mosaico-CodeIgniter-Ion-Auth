<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="Title" content="<?php echo $title; ?>"  />
    <meta name="viewport" content="width=1024, initial-scale=1">
    <title><?php echo $title; ?></title>
    <link rel="canonical" href="http://mosaico.io" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo base_url(); ?>mosaico/favicon.ico" type="image/x-icon" />

    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/jquery.min.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/knockout.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/jquery-ui.min.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/jquery.ui.touch-punch.min.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/load-image.all.min.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/canvas-to-blob.min.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/jquery.iframe-transport.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/jquery.fileupload.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/jquery.fileupload-process.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/jquery.fileupload-image.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/jquery.fileupload-validate.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/knockout-jqueryui.min.js"></script>
    <script src="<?php echo base_url(); ?>mosaico/dist/vendor/tinymce.min.js"></script>

    <script src="<?php echo base_url(); ?>mosaico/dist/mosaico.min.js?v=0.16"></script>
    <script>
$(function() {
  if (!Mosaico.isCompatible()) {
    alert('Update your browser!');
    return;
  }
  var server = '<?php echo base_url(); ?>';
  var basePath = server + 'mosaico';
  if (basePath.lastIndexOf('#') > 0) basePath = basePath.substr(0, basePath.lastIndexOf('#'));
  if (basePath.lastIndexOf('?') > 0) basePath = basePath.substr(0, basePath.lastIndexOf('?'));
  if (basePath.lastIndexOf('/') > 0) basePath = basePath.substr(0, basePath.lastIndexOf('/'));
  // plugin for integrating save button
    var hash = window.location.href.split("#").pop();
    var name = '<?php echo isset($name) ? $name : ''; ?>';
    
  var plugins = [
    function(viewModel) {
        var saveCmd = {
            name: 'Save', // l10n happens in the template
            enabled: ko.observable(true)
        };
        
        /*$.ajaxSetup({
            data: {
                <?php echo $csrf_token; ?>: '<?php echo $csrf_hash; ?>'
            }
        });*/
        

        saveCmd.execute = function() {
            saveCmd.enabled(false);
            if (typeof viewModel.metadata.created == 'undefined') {
                viewModel.metadata.created = Date.now();
            }
            viewModel.metadata.changed = Date.now();
            viewModel.metadata.name = '<?php echo isset($name) ? $name : ''; ?>';
            if (typeof viewModel.metadata.key == 'undefined') {
                viewModel.metadata.key = '<?php echo uniqid('', true); ?>';
            }

            // This is the simplest for sending it as POST
            var postData = {
                hash: viewModel.metadata.key,
                name: '<?php echo isset($name) ? $name : ''; ?>',
                metadata: viewModel.exportMetadata(),
                content: viewModel.exportJSON(),
                html: viewModel.exportHTML()
            };

            $.post( server + 'Email_templates/save_template/', postData)
                .done(function() {
                    viewModel.notifier.success(viewModel.t('Successfully saved.'));
                })
                .fail(function(jqXHR, textStatus, error) {
                    console.log(textStatus);
                    console.log(error);
                    console.log(jqXHR);
                    viewModel.notifier.error(viewModel.t('Saving failed. Please try again in a few moment or contact us.'));
                })
                .always(function() {
                    saveCmd.enabled(true);
                }
            );
        }
        
        var downloadCmd = {
            name: 'Download', // l10n happens in the template
            enabled: ko.observable(true)
        };



        downloadCmd.execute = function() {
            downloadCmd.enabled(false);

            var form = $('<form></form>').attr('action', server + 'Email_templates/ProcessDlRequest/').attr('method', 'post');
            form.append($("<input></input>").attr('type', 'hidden').attr('name', 'action').attr('value', "download"));
            form.append($("<input></input>").attr('type', 'hidden').attr('name', 'html').attr('value', viewModel.exportHTML()));
            form.append($("<input></input>").attr('type', 'hidden').attr('name', 'filename').attr('value', name + '.html'));
            form.appendTo('body').submit().remove();
            
            downloadCmd.enabled(true);
        }
        

        var testCmd = {
            name: 'Test', // l10n happens in the template
            enabled: ko.observable(true)
        };
        


        testCmd.execute = function() {
            testCmd.enabled(false);
            // This is the simplest for sending it as POST
            var email = prompt("Enter the email of the recipient");
            var subject = prompt("Enter the main subject of email");
            var postData = {
                action: "email",
                html: viewModel.exportHTML(),
                rcpt: email,
                subject: subject
            };

            $.post( server + 'Email_templates/ProcessDlRequest/', postData)
                .success(function() {
                    viewModel.notifier.success(viewModel.t('Email sent successfully.'));
                })
                .fail(function(jqXHR, textStatus, error) {
                    console.log(textStatus);
                    console.log(error);
                    console.log(jqXHR);
                    viewModel.notifier.error(viewModel.t(jqXHR.responseJSON.msg));
                })
                .always(function() {
                    testCmd.enabled(true);
                }
            );
        }
        
        viewModel.save = saveCmd;
        viewModel.download = downloadCmd;
        viewModel.test = testCmd;
        viewModel.logoPath = server + 'mosaico/dist/img/mosaico32.png';
        viewModel.logoUrl = server + 'Email_templates/';
        return viewModel;
    }
    ];
    
    // mosaico template 
    var template = '<?php echo isset($template) ? $template : ''; ?>';
    
    $.ajax({
            url:  server + 'Email_templates/get_template/' + hash, // Path to load.php
            type: 'get',
            dataType: 'JSON',
            success: function(data){
                console.log(data);
                var metadata = data.template_metadata;
                var content = data.template_content;
                var ok = Mosaico.start({
                    imgProcessorBackend: server + 'Email_templates/ProcessImgRequest/',
                    emailProcessorBackend: server + 'Email_templates/ProcessDlRequest/',
                    titleToken: "<?php echo $title; ?>",
                    fileuploadConfig: {
                      url: server + 'Email_templates/ProcessUploadRequest/',
                      // messages??
                    }
                }, template, $.parseJSON(metadata) /* metadata */, $.parseJSON(content) /* model */, plugins);
                if (!ok) {
                    console.log("Missing initialization hash, redirecting to main entrypoint"+ok);
                    //document.location = server + '';
                }
            },
            statusCode: {
                404: function() {
                    var metadata = {};
                    var content = {};
                    metadata.key = hash;
                    metadata.template = template;

                    var ok = Mosaico.start({
                        imgProcessorBackend: server + 'Email_templates/ProcessImgRequest/',
                        emailProcessorBackend: server + 'Email_templates/ProcessDlRequest/',
                        titleToken: "<?php echo $title; ?>",
                        fileuploadConfig: {
                          url: server + 'Email_templates/ProcessUploadRequest/',
                          // messages??
                        }
                    }, template, (metadata) /* metadata */, (content) /* model */, plugins);
                    if (!ok) {
                        console.log("Missing initialization hash, redirecting to main entrypoint"+ok);
                        //document.location = server + '';
                    }
                }
            }
    });
});
    </script>
    
    <link rel="stylesheet" href="<?php echo base_url(); ?>mosaico/dist/mosaico-material.min.css?v=0.10" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>mosaico/dist/vendor/notoregular/stylesheet.css" />
  </head>
  <body class="mo-standalone">


  </body>
</html>
