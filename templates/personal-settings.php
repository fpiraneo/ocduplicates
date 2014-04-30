<fieldset class="personalblock">
    <h2>ownCloud duplicates</h2>

    <div>
        <strong><?php p($l->t('Generate empty signatures')); ?></strong>
        <table style="width:100%">
            <tr>
                <td>
                    <p><?php p($l->t('Generate signatures for documents with empty signatures; this feature can require long time!')); ?></p>
                    <p id="woSignature" />
                </td>
                <td style="text-align: right;">
                    <button id="ocduplicates_genEmptySign_start" />
                    <label for="ocduplicates_genEmptySign_start" style="padding:0px 10px 0px 10px;">
                        <?php p($l->t('Start')) ?>
                    </label>                
                </td>
            </tr>
        </table>
    </div>

    <div>
        <strong><?php p($l->t('Generate all signatures')); ?></strong>
        <table style="width:100%">
            <tr>
                <td>
                    <p><?php p($l->t('Generate signatures for ALL the documents; this feature can require VERY LONG time!')); ?></p>
                    <p id="docsCount" />
                </td>
                <td style="text-align: right;">
                    <button id="ocduplicates_genAllSign_start" />
                    <label for="ocduplicates_genAllSign_start" style="padding:0px 10px 0px 10px;">
                        <?php p($l->t('Start')) ?>
                    </label>                
                </td>
            </tr>
        </table>
    </div>

    <div>
        <strong><?php p($l->t('Progress bar')); ?></strong>
        <div id="ocduplicates_progress" style="width: 100%;">
            <div id="ocduplicates_progresslabel" style="text-align: center; width: 100%;">
                <?php p($l->t('Waiting for start')) ?>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        <strong><?php p($l->t('Hunt for duplicates')); ?></strong>
        <table style="width:100%">
            <tr>
                <td>
                    <p><?php p($l->t('Press')); ?> <strong><?php p($l->t('Start')); ?></strong> <?php p($l->t('to search through your ownCloud space for files duplicates.')); ?> 
                    <?php p($l->t('The file')); ?> <strong>duplicates.txt</strong> <?php p($l->t('will be created on your root directory.')); ?></p>
                    <p id="duplicates" />
                </td>            
                <td style="text-align: right;">
                    <button id="ocduplicates_start" />
                    <label for="ocduplicates_start" style="padding:0px 10px 0px 10px;">
                        <?php p($l->t('Start')) ?>
                    </label>                
                </td>
            </tr>
        </table>
    </div>
    
</fieldset>
