function js_form_design(type,token) {
    if (type != "") { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("js_form_design").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","assets/fb_createdatabase.php?form_design="+type+"&token="+token,true);
        xmlhttp.send();
    }
}
function js_form_preview(type,token) {
    if (type != "") { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("js_form_preview").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","assets/fb_createdatabase.php?form_preview="+type+"&token="+token,true);
        xmlhttp.send();
    }
}

function js_div_number(type) {
    var div1 = document.getElementById("div_class1");
    var div2 = document.getElementById("div_class2");
    var divvalue;
    
    if(div2){
        divvalue = div2.value; 
    }else if(div1){
        divvalue = div1.value; 
    }else {
        divvalue = "";
    }
    
    if (type != "") { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("div_number").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","assets/fb_createform.php?div_number="+type+"&div_value="+divvalue,true);
        xmlhttp.send();
    }
}

function js_input_type(type) {
    if (type != "") { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("type_insert").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","assets/fb_createform.php?type="+type,true);
        xmlhttp.send();
    }
}
function js_input_style(type) {
    if (type != "") { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("js_input_style").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","assets/fb_createform.php?style="+type,true);
        xmlhttp.send();
    }
}
function database_addrow() {
  var table = document.getElementById("table_database");
  var row = table.insertRow(-1);
  var cell1 = row.insertCell(0);
  var cell2 = row.insertCell(1);
  var cell3 = row.insertCell(2);
  cell1.innerHTML = '<input type="text" class="form-control" name="databaseid[]" id="databaseid" />';
  cell2.innerHTML = '<input type="text" class="form-control" name="databasevalue[]" id="databasevalue" />';
  cell3.innerHTML = '<input type="button" class="btn btn-danger" value="Delete" onclick="deleteRow(this)">';
}
function deleteRow(r) {
  var i = r.parentNode.parentNode.rowIndex;
  document.getElementById("table_database").deleteRow(i);
}

