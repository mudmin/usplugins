<?php
if(!isset($msgSettings)){ //menu hook removed
 $msgSettings = $db->query("SELECT * FROM plg_msg_settings")->first();
}
$multi = 0;
$type = Input::get('type');
if ($type == "") {
  $type = "all";
}
if ($type == 1) {
  $none = "No alerts found.";
} elseif ($type == 2) {
  $none = "No notifications found.";
} elseif ($type == 3) {
  $none = "No messages found.";
} else {
  $none = "No messages found.";
}
?>

<div class="modal fade messages-modal" id="messagesModal" tabindex="-1" aria-labelledby="messagesModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 90vw; min-width:90vw;">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="messagesModalLabel"></h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close Messages"></button>
      </div>


      <div class="modal-body">

      <div class="category-buttons-responsive allBtn">

        <a href="#" data-category="all" class="btn btn-outline-primary btn-sm allBtn">All</a>
        <?php if($msgSettings->alerts == 1){ $multi++; ?>
        <a href="#" data-category="1" class="btn btn-outline-primary btn-sm">Alerts</a>
        <?php } ?>
        <?php if($msgSettings->notifications == 1){ $multi++; ?>
        <a href="#" data-category="2" class="btn btn-outline-primary btn-sm">Notifications</a>
        <?php } ?>
        <?php if($msgSettings->messages == 1){ $multi++; ?>
        <a href="#" data-category="3" class="btn btn-outline-primary btn-sm">Messages</a>
        <?php } 
          if($multi < 2){
            $leftClass = "d-none";
            $rightClass = "col-12";
          }else{
            $leftClass = "col-12 col-lg-2";
            $rightClass = "col-12 col-lg-10";
          }
        ?>
        </div>
        <div class="row">
          <div class="<?=$leftClass?> email-navigation">
            <!-- Sidebar with category links -->
            <div class="card" style="min-height: 75vh;">
              <div class="card-header">
                <h5 class="allBtn">Categories</h5>
                <div class="input-group input-group-sm">
                </div>
              </div>
              <div class="card-body filter-meeting-card-body" style="background-color:white;">
                <div class="list-group list-group-flush">
                  <a href="#" data-category="all" class="list-group-item message-category close-message active d-flex align-items-center allBtn">
                    <i class='bx bxs-inbox me-3 font-20 allBtn'></i><span>All</span>
                  </a>
                  <?php if($msgSettings->alerts == 1){ ?>
                  <a href="#" data-category="1" class="list-group-item message-category close-message d-flex align-items-center">
                    <i class='bx bxs-alarm-snooze me-3 font-20'></i><span>Alerts</span>
                  </a>
                  <?php } ?>
                  <?php if($msgSettings->notifications == 1){ ?>
                  <a href="#" data-category="2" class="list-group-item message-category close-message d-flex align-items-center">
                    <i class='bx bxs-send me-3 font-20'></i><span>Notifications</span>
                  </a>
                  <?php } ?>
                  <?php if($msgSettings->messages == 1){ ?>
                  <a href="#" data-category="3" class="list-group-item message-category close-message d-flex align-items-center">
                    <i class='bx bxs-star me-3 font-20'></i><span>Messages</span>
                  </a>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
          <div class="<?=$rightClass?> mb-2 tighten-left tighten-right">
            <!-- Content area for messages -->
            <div class="card" style="min-height: 75vh; max-height: 75vh; overflow-y:scroll;">
              <div class="email-content" style="display: none; padding: 2rem;">
                <!-- Message content goes here -->
                <div class="card">
                  <div class="card-header message-header">
                    <div class="row">
                      <div class="col-12 col-md-6">
                        <p class="message-from" id="messageFrom">From: Sender Name</p>
                      </div>
                      <div class="col-12 col-md-6 text-md-end">
                        <p class="message-date" id="messageDate">Date: January 1, 2023</p>
                      </div>
                    </div>

                  </div>
                  <div class="card-body">
                    <!-- Add a subheading for the message title -->
                    <div class="row">
                      <div class="col-12">
                        <p><span class="message-icon" id="messageIcon"><b></span>Subject: </b><span class="message-title" id="messageTitle">Message Title</span></p>
                        <hr>
                      </div>
                    </div>
                    <div class="message-body" id="messageModalBody">
                      <!-- Message Content Goes Here -->
                    </div>
                    <div class="text-right text-end pull-right">
                      <button type="button" class="btn btn-secondary close-message btn-sm">Close Message</button>
                    </div>

                  </div>
                </div>
              </div>


              <table class="table omt-table email-list">
                <thead>
                  <tr>
                    <th></th>
                    <th>From</th>
                    <th>Subject</th>
                    <th>
                      <div class="d-flex justify-content-between align-items-center">
                        <span>Date Sent</span>
                        <button class="deleteChecked btn btn-outline-danger btn-sm ms-3 py-0">Delete Checked</button>
                      </div>
                    </th>

                    <th>
                      <input type="checkbox" id="checkAllMessageDelBoxes">

                    </th>
                  </tr>
                </thead>
                <tbody class="messagesHere">

                </tbody>
              </table>
              <div class="text-center no-messages email-list">

              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Messages</button>
      </div>
    </div>
  </div>
</div>
<style>
  .unread {
    font-weight: bold;
  }

  .hidden {
    display: none;
  }

  .message-category.active:hover {
    background-color: var(--bs-list-group-active-bg);
    cursor: pointer;
  }

  .message-row {
    cursor: pointer;
  }

  .type1,
  .type2,
  .type3 {
    display: none;
  }

  @media (max-width: 1199px) { 
  .email-navigation {
    display: none; /* Hide sidebar on smaller screens */
  }
  
  .category-buttons-responsive {
    display: block; /* Show the new buttons row */
    overflow-x: auto; /* Allows horizontal scrolling if there are many buttons */
    white-space: nowrap; /* Keeps buttons in one line */
    margin-bottom: 1rem; /* Adds some space below the buttons */
  }
  
  .col-12.col-md-10 {
    flex: 0 0 100%;
    max-width: 100%;
  }
}

<?php if($multi < 2){ ?>
  .allBtn{
    display: none !important;
  }
<?php } ?>


</style>
<script>
  let deleted = [];
  $(document).ready(function() {
    // Initialize modal display
    $('#messagesModal').modal({
      show: false
    });

    // Fetch all messages initially
    fetchMessages();

    // Function to display messages
    function displayMessages(category, messages) {
      let tableBody = $('.messagesHere');
      tableBody.empty(); // Clear existing messages

      messages.forEach(function(message) {
        let row = generateMessageRow(message);
        tableBody.append(row);
      });

      // Adjust visibility based on category
      filterMessagesByCategory(category, "189");
    }

    // Helper function to replace null with empty string or "System" and "Message"
    function replaceNullOrEmpty(value, replacement) {
      return value !== null ? value : replacement;
    }
    // Function to generate the HTML for a message row
    function generateMessageRow(message) {

      let fname = replaceNullOrEmpty(message.fname, 'System');
      let lname = replaceNullOrEmpty(message.lname, 'Message');
      console.log(message);
      return `
      <tr class="msg${message.id} type${message.msg_type} message-row" data-id="${message.id}">
        <td class="loadMessage" data-id="${message.id}">${getCategoryIcon(message.msg_type)}</td>
        <td class="loadMessage" data-id="${message.id}"><span class="${message.msg_read === 0 ? 'unread' : ''}" id="msg-${message.id}">${fname} ${lname}</span></td>
        <td class="loadMessage" data-id="${message.id}">${message.title}</td>
        <td class="loadMessage" data-id="${message.id}">${message.date}</td>
        <td><input class="messageDelBox" type="checkbox" data-id="${message.id}"></td>
      </tr>
    `;
    }

    // Function to filter messages by category
    function filterMessagesByCategory(category, diag = false) {
      console.log("filtering by category: " + category);
      console.log("Filtering diag: " + diag);
      if (category === 'all') {
        $('.type1').css('display', 'table-row');
        $('.type2').css('display', 'table-row');
        $('.type3').css('display', 'table-row');

      } else if (category == 1) {
        $('.type1').css('display', 'table-row');
        $('.type2').css('display', 'none');
        $('.type3').css('display', 'none');
      } else if (category == 2) {
        $('.type1').css('display', 'none');
        $('.type2').css('display', 'table-row');
        $('.type3').css('display', 'none');
      } else if (category == 3) {
        $('.type1').css('display', 'none');
        $('.type2').css('display', 'none');
        $('.type3').css('display', 'table-row');
      }
    }


    // Event listener for category filter clicks
    $('.message-category').click(function(e) {
      e.preventDefault();
      $('.message-category').removeClass('active');
      $(this).addClass('active');
      let category = $(this).data('category');
      filterMessagesByCategory(category, "244");
    });

    // Event listener for loading a specific message
    $(document).on("click", ".loadMessage", function() {
      var messageId = $(this).closest('tr').data("id");
      loadSpecificMessage(messageId);
    });

    function loadSpecificMessage(messageId) {
      var formData = {
        'id': messageId,
      };


      $.ajax({
          type: 'POST',
          url: '<?= $us_url_root ?>usersc/plugins/messaging/assets/parsers/fetchMessage.php',
          data: formData,
          dataType: 'json',
        })
        .done(function(data) {
          console.log(data);
          if (data.success == false) {
            alert("Message not found");
          } else {
            console.log(data.msg);
            $("#id").val(messageId);

            // Set modal header background color and icon based on msg_type
            var msgType = data.msg.msg_type;
            var headerColor = "";
            var iconClass = "";

            switch (msgType) {
              case 1:
                headerColor = "#EF0000";
                iconClass = "fas fa-1x fa-exclamation-circle";
                break;
              case 2:
                headerColor = "#FFAC1D";
                iconClass = "fas fa-1x fa-bell";
                break;
              case 3:
                headerColor = "#007BFF";
                iconClass = "fas fa-1x fa-envelope";
                break;
              default:
                // Handle other cases if needed
                break;
            }

            // Set the modal header content and background color
            // var modalHeader = $(".modal-header");
            // modalHeader.css('background-color', headerColor);
            $(".message-title").html(`${data.msg.title}`);
            $(".message-icon").html(`<i class="${iconClass} me-2" style="color: ${headerColor}"></i>`);

            showMessageContent();

            $("#messageModalBody").html(data.msg.message);
            console.log(data.msg.from_name);
            $("#messageFrom").html(data.msg.from_name);
            $("#messageDate").html(data.msg.date);
            $("#messageBody").html(data.msg.message);
            $("#msg-" + messageId).removeClass("unread");
          }
        });
    }


    // Delete checkboxes
    $("#checkAllMessageDelBoxes").click(function() {
      $(".messageDelBox").prop('checked', $(this).prop('checked'));
    });

    //deleteMessages ajax call
    $(document).on("click", ".deleteChecked", function() {
      console.log("attempting to delete messages");
      var checked = [];
      $(".messageDelBox:checked").each(function() {
        checked.push($(this).data('id'));
      });
      console.log(checked);
      if (checked.length > 0) {

        var formData = {
          'checked': checked,
        };

        $.ajax({
            type: 'POST',
            url: '<?= $us_url_root ?>usersc/plugins/messaging/assets/parsers/deleteMessages.php',
            data: formData,
            dataType: 'json',
          })
          .done(function(data) {
            console.log(data);
            $("#checkAllMessageDelBoxes").prop('checked', false);
            if (data.success == false) {
              alert("Message not found");
            } else {
              console.log(data.msg);
              //remove row from table
              for (var i = 0; i < checked.length; i++) {
                let messageId = checked[i];
                // Remove the rows from all tables with the matching data-id
                $(".msg" + messageId).remove();
                console.log("msg" + messageId);
                deleted.push(messageId);
                console.log(deleted);
              }

            }
          });
      }
    });

    $(document).on("click", ".openMessageButton", function() {
      let initialCategory = $(this).data('initial-category') || 'all';
      console.log("Initial category: ", initialCategory);

      // Fetch messages first
      fetchMessages(function() {
        // Pre-select the category before showing the modal
        preSelectCategory(initialCategory, function() {
          $('#messagesModal').modal('show');
        });
      });
    });

    // Adjust fetchMessages function to accept a callback
    function fetchMessages(callback) {
      var formData = {
        'method': 'fetchMessages'
      };
      $.ajax({
        type: 'POST',
        url: '<?= $us_url_root ?>usersc/plugins/messaging/assets/parsers/fetchMessages.php',
        data: formData,
        dataType: 'json',
      }).done(function(data) {
        if (data.success == true) {
          displayMessages('all', data.messages); // Display all messages initially
          if (typeof callback === "function") {
            callback(); // Call the callback function if provided
          }
        }
      });
    }

    // Adjust preSelectCategory function to use a callback for modal display
    function preSelectCategory(category, callback) {
      $('.message-category').removeClass('active');
      $(`.message-category[data-category="${category}"]`).addClass('active');
      filterMessagesByCategory(category);

      if (typeof callback === "function") {
        callback(); // Ensure modal is shown after category selection
      }
    }


    // Helper function to get the icon based on message type
    function getCategoryIcon(msgType) {
      switch (msgType) {
        case 1:
          return '<i class="ms-2 fas fa-exclamation-circle text-danger"></i>';
        case 2:
          return '<i class="ms-2 fas fa-bell text-warning"></i>';
        case 3:
          return '<i class="ms-2 fas fa-envelope text-primary"></i>';
        default:
          return '';
      }
    }

    $(document).on("click", ".close-message", function() {
      hideMessageContent(); // Hide the message content and show the list
    });

    function showMessageContent() {
      $('.email-content').show();
      $('.email-list').hide();
    }

    // Function to hide message content and show message list
    function hideMessageContent() {
      $('.email-content').hide();
      $('.email-list').show();
    }

    //responsive buttons
    adjustCategoryButtonsVisibility();
    $(window).resize(adjustCategoryButtonsVisibility);

    // Listen for the modal show event
    $('#messagesModal').on('show.bs.modal', function () {
      updateCategoryButtonActiveState();
    });

    function adjustCategoryButtonsVisibility() {
  if ($(window).width() < 1200) { // Adjusted from 920 to 1200
    $('.category-buttons-responsive').removeClass('d-none');
  } else {
    $('.category-buttons-responsive').addClass('d-none');
  }
}

    $('.category-buttons-responsive a').on('click', function(e) {
      e.preventDefault();
      var category = $(this).data('category');
      filterMessagesByCategory(category.toString(), "Dynamically updated");
      updateActiveCategoryButton($(this));
    });

    function updateCategoryButtonActiveState() {
      // Assuming 'activeCategory' is the variable holding the current category
      var activeCategory = $('.message-category.active').data('category'); 
      $('.category-buttons-responsive a').each(function() {
        var category = $(this).data('category');
        if (category == activeCategory) {
          updateActiveCategoryButton($(this));
        }
      });
    }

    function updateActiveCategoryButton(activeButton) {
      $('.category-buttons-responsive a').removeClass('btn-primary').addClass('btn-outline-primary');
      activeButton.removeClass('btn-outline-primary').addClass('btn-primary');
    }
  });
</script>