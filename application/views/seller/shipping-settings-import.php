<div class="container">
    <div clas="row">
        <span>
            Please Follow The Sample Data format, to import CSV file.
        </span>
    </div>

    <div class="row mt-3">
        <form id="frmImportExport" name="frmImportExport" method="post" enctype="multipart/form-data" class="form form--horizontal" onsubmit="importShippingSettingsFile('importShippingSettingsData',21); return false;">
            <div class="col-md-12">
                    <div class="field-set"><div class="caption-wraper">
                        <label class="field_label">File To Be Uploaded:</label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <div class="filefield">
                                <span class="filename" id="importFileName"></span>
                                <input id="import_file" onchange="$('#importFileName').html(this.value)" data-field-caption="File To Be Uploaded:" data-fatreq="{&quot;required&quot;:false}" type="file" name="import_file" value="">
                                <label class="filelabel">Browse File</label>
                            </div>
                            <small>Invalid Data Will Not Be Processed</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    
                    <div class="field-wraper">
                        <div class="field_cover">
                            <input data-field-caption="" data-fatreq="{&quot;required&quot;:false}" type="submit" name="btn_submit" value="Submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>