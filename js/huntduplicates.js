$('document').ready(function() {
    $('#ocduplicates_progress').progressbar({
        value: 0
    });
    
    updateCounters();
    
    $('#ocduplicates_genAllSign_start').on('click', function() {
        $('#ocduplicates_genAllSign_start').attr('disabled', 'disabled');
        actionSignatures(1);
        $('#ocduplicates_genAllSign_start').removeAttr('disabled');
    });
    
    $('#ocduplicates_genEmptySign_start').on('click', function() {
        $('#ocduplicates_genEmptySign_start').attr('disabled', 'disabled')
        actionSignatures(0);
        $('#ocduplicates_genEmptySign_start').removeAttr('disabled');
    });

    function actionSignatures(allFiles) {
        $.ajax({
            url: OC.filePath('ocduplicates', 'ajax', 'getFilesList.php'),
            timeout: 5000,
            async: false,

            data: {
                getAll: allFiles
            },

            type: 'POST',

            success: function(rawResult) {
                var filesList = JSON.parse(rawResult);
                var filesQty = filesList.length;
                var failed = 0;
                
                for(var iter = 0; iter < filesQty; iter++) {
                    var result = generateSignature(filesList[iter]);
                    
                    failed += (result) ? 0 : 1;
                    updatePB(iter, filesQty);
                }
                
                if(failed === 0) {
                    updateStatusBar(t('ocduplicates', 'All signatures generated successfully!'));
                } else {
                    updateStatusBar(t('ocduplicates', 'Unable to generate ' + failed + ' signatures!'));
                }
            },

            error: function( xhr, status ) {
                updateStatusBar(t('ocduplicates', 'Ajax error retreiving files list!'));
            }
        });
        
        $('#ocduplicates_progress').progressbar('value', 0);
        $('#ocduplicates_progresslabel').text(t('ocduplicates', 'Waiting for start'));

        updateCounters();
    }
    
    function updateCounters() {
        $.ajax({
            url: OC.filePath('ocduplicates', 'ajax', 'getFilesList.php'),
            timeout: 5000,
            async: false,

            data: {
                getCounts: 1
            },

            type: 'POST',

            success: function(rawResult) {
                var counts = JSON.parse(rawResult);
                
                $('#docsCount').text(counts['docCount'].toString());
                $('#docsCount').append(t('ocduplicates', ' documents in your cloud!'));
                
                $('#woSignature').text(counts['woSignature'].toString());
                $('#woSignature').append(t('ocduplicates', ' documents doesn\'t have signature!'));

                $('#duplicates').text(t('ocduplicates', 'Actually '));
                $('#duplicates').append(counts['duplicates'].toString());
                $('#duplicates').append(t('ocduplicates', ' documents seems to be duplicated on your cloud.'));
                
                if(counts['duplicates'] === 0) {
                    $('#ocduplicates_start').attr('disabled', 'disabled');
                } else {
                    $('#ocduplicates_start').removeAttr('disabled');
                }
            }
        });
    }
    
    function updatePB(done, total) {
        var percentage = done / total * 100;
        $('#ocduplicates_progress').progressbar("value", percentage);
        $('#ocduplicates_progresslabel').text(t('ocduplicates', 'Processed ') + done + t('ocduplicates', ' of ') + total);
    }
    
    function generateSignature(fileID) {
        var result = $.ajax({
            url: OC.filePath('ocduplicates', 'ajax', 'setFileSignature.php'),
            timeout: 5000,
            async: false,

            data: {
                fileID: fileID
            },

            type: 'POST'
        });
        
        return (result.responseText === '1');
    }
        
    $('#ocduplicates_start').on('click', function() {
        $('#ocduplicates_start').attr('disabled', 'disabled');

        $.ajax({
            url: OC.filePath('ocduplicates', 'ajax', 'generateDuplicatesList.php'),
            timeout: 5000,
            async: false,

            data: {                    
            },

            type: 'POST',

            success: function(rawResult) {
                if(rawResult === '0')
                    updateStatusBar(t('ocduplicates', 'Duplicates file generated successfully!'));
                else
                    updateStatusBar(t('ocduplicates', 'Error generating duplicates file!'));
            },

            error: function( xhr, status ) {
                updateStatusBar(t('ocduplicates', 'Ajax error generating duplicates file!'));
            }
        });        
        
        // Update user's interface        
        $('#ocduplicates_start').removeAttr('disabled');
    });
    
    function updateStatusBar( t ) {
        $('#notification').html(t);
        $('#notification').slideDown();
        window.setTimeout(function(){
            $('#notification').slideUp();
        }, 5000);            
    }    
});