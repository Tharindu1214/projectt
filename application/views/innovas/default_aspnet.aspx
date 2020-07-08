<%@ Page Language="vb" ValidateRequest="false" Debug="true" %>
<%@ Register TagPrefix="editor" Assembly="WYSIWYGEditor" namespace="InnovaStudio" %>

<script language="VB" runat="server">
    Sub Page_Load(Source As Object, E As EventArgs)
        
        If Not Page.IsPostBack Then
            oEdit1.Text = "<p>First Paragraph here. Lorem ipsum fierent mnesarchum ne vel, et usu posse takimata omittantur, pro ut tale erant sapientem. Et regione tibique ancillae nam. Tale modus iuvaret eu usu.</p>"
        End If
   
        'Editor Dimension
        oEdit1.Width = 850
        oEdit1.Height = 350
        
        'Enable Features
        oEdit1.EnableFlickr = True
        oEdit1.EnableCssButtons = True
        oEdit1.EnableTableAutoFormat = True
        oEdit1.EnableLightbox = True
        
        'Add Custom Buttons
        oEdit1.ToolbarCustomButtons.Add(New CustomButton("MyCustomButton", "alert('Run custom command..')", "Caption here", "btnCustom1.gif"))

        'Toolbar Buttons Configuration
        Dim tabHome As InnovaStudio.ISTab
        Dim grpEdit1 As InnovaStudio.ISGroup = New InnovaStudio.ISGroup("grpEdit1", "", New String() {"Bold", "Italic", "Underline", "FontDialog", "ForeColor", "TextDialog", "RemoveFormat"})
        Dim grpEdit2 As InnovaStudio.ISGroup = New InnovaStudio.ISGroup("grpEdit2", "", New String() {"Bullets", "Numbering", "JustifyLeft", "JustifyCenter", "JustifyRight"})
        Dim grpEdit3 As InnovaStudio.ISGroup = New InnovaStudio.ISGroup("grpEdit3", "", New String() {"LinkDialog", "ImageDialog", "YoutubeDialog", "TableDialog", "Emoticons"})
        Dim grpEdit4 As InnovaStudio.ISGroup = New InnovaStudio.ISGroup("grpEdit4", "", New String() {"InternalLink", "CustomObject", "MyCustomButton", "CustomTag"})
        Dim grpEdit5 As InnovaStudio.ISGroup = New InnovaStudio.ISGroup("grpEdit5", "", New String() {"Undo", "Redo", "FullScreen", "SourceDialog"})
        tabHome = New InnovaStudio.ISTab("tabHome", "Home")
        tabHome.Groups.AddRange(New InnovaStudio.ISGroup() {grpEdit1, grpEdit2, grpEdit3, grpEdit4, grpEdit5})
        oEdit1.ToolbarTabs.Add(tabHome)
        
        'Define "InternalLink" & "CustomObject" buttons
        oEdit1.InternalLink = "my_custom_dialog.htm"
        oEdit1.InternalLinkWidth = 650
        oEdit1.InternalLinkHeight = 350
        oEdit1.CustomObject = "my_custom_dialog.htm"
        oEdit1.CustomObjectWidth = 650
        oEdit1.CustomObjectHeight = 350
        
        '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        'Enable Custom File Browser: 
        
        'Important Note
        'FILE BROWSER/MANAGER IS A FREE ADD-ON INCLUDED IN INNOVASTUDIO LIVE EDITOR PACKAGE (in assetmanager folder).
        'SINCE IT IS A STANDALONE APPLICATION AND CAN BE ACCESSED DIRECTLY FROM BROWSER, 
        'YOU WILL NEED TO SECURE IT BY ADDING USER CHECK/AUTHENTICATION TO:

        ' - assetmanager/asset.[aspx/asp/php]

        'SECURITY CHECK MUST ALSO BE ADDED TO OTHER FILES IN FILE BROWSER FOLDER SUCH AS:

        ' - assetmanager/server/delfile.[ashx/asp/php]
        ' - assetmanager/server/delfolder.[ashx/asp/php]
        ' - assetmanager/server/newfolder.[ashx/asp/php]
        ' - assetmanager/server/upload.[ashx/asp/php]  

        'oEdit1.fileBrowser = "/liveeditor/assetmanager/asset.aspx"
        '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        
        'Apply stylesheet for the editing content
        oEdit1.Css = "styles/default.css"
                
        'Define "CustomTag" dropdown
        oEdit1.CustomTags.add(new Param("First Name","{%first_name%}"))
        oEdit1.CustomTags.add(new Param("Last Name","{%last_name%}"))
        oEdit1.CustomTags.Add(New Param("Email", "{%email%}"))
       
        
        'Editing mode
        'oEdit1.EditMode = EditorModeEnum.XHTML
    End Sub

    Sub Button1_Click(Source As System.Object, E As System.EventArgs)
        'Label1.Text = "<div style=""padding:0px 20px;border:#000000 1px dashed;"">" & oEdit1.Text & "</div>"
        Label1.Text = oEdit1.Text
    End Sub
</script>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <!-- OPTIONAL: This section is required only if using Google Fonts Dialog AND required on the page where the content is published -->
    <script src="scripts/common/jquery-1.7.min.js" type="text/javascript"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js" type="text/javascript"></script>
    <script src="scripts/common/webfont.js" type="text/javascript"></script>

    <link href="styles/default.css" rel="stylesheet" type="text/css" />

    <style type="text/css">
        h1, h2, h3 {text-shadow: 1px 1px 0px rgba(255, 255, 255, 0.8);}
        h2 {text-transform:uppercase}
        h3 {font-size:14px;color:#a90000;border-bottom:#000000 1px dotted;}
    </style>

</head>
<body style="margin:50px;">

<a href="default.htm">Default Example</a> | <a href="default_full.htm">More Examples</a> | <a href="docs.htm">Documentation</a> | <a href="docs_aspnet.htm">ASP.NET Documentation</a> | ASP.NET Example

<h2>ASP.NET Demo</h2>

<form id="Form1" method="post" runat="server" style="margin:0">

<editor:wysiwygeditor 
    Runat="server"
    scriptPath="scripts/"
    ID="oEdit1" />
<br />  
<asp:button runat="server" CssClass="button" onclick="Button1_Click" Text="SUBMIT" ID="btnSubmit" />

<br />
<div id="preview" style="width:850px;">
    <asp:label id="Label1" runat="server"/>
</div>


</form>

</body>
</html>