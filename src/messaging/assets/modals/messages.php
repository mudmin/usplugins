<?php
if (isset($plgMessages)) {

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
  <style>
    .message-category:hover {
      background-color: #e9ecef !important;
      cursor: pointer;
    }

    .message-category.active,
    .message-category.active:hover {
      background-color: #6c757d !important;
      color: #fff !important;
      cursor: pointer;
    }

    .message-category.active span,
    .message-category.active i,
    .message-category.active:hover span,
    .message-category.active:hover i {
      color: #fff !important;
    }

    /* Force scrolling on messages container - highly specific to prevent override */
    #messagesModal .plg-msg-scroll-container {
      min-height: 75vh !important;
      max-height: 75vh !important;
      height: 75vh !important;
      overflow-y: scroll !important;
      overflow-x: hidden !important;
      overflow: hidden scroll !important;
      -webkit-overflow-scrolling: touch !important;
      display: block !important;
      position: relative !important;
    }

    /* Ensure scrollbar is always visible */
    #messagesModal .plg-msg-scroll-container::-webkit-scrollbar {
      width: 12px !important;
      display: block !important;
    }

    #messagesModal .plg-msg-scroll-container::-webkit-scrollbar-track {
      background: #f1f1f1 !important;
      display: block !important;
    }

    #messagesModal .plg-msg-scroll-container::-webkit-scrollbar-thumb {
      background: #888 !important;
      border-radius: 4px !important;
      min-height: 40px !important;
    }

    #messagesModal .plg-msg-scroll-container::-webkit-scrollbar-thumb:hover {
      background: #555 !important;
    }

    /* Firefox scrollbar */
    #messagesModal .plg-msg-scroll-container {
      scrollbar-width: auto !important;
      scrollbar-color: #888 #f1f1f1 !important;
    }

    /* Ensure parent elements don't clip the scrollbar */
    #messagesModal .modal-body {
      overflow: visible !important;
    }

    #messagesModal .modal-body .row {
      overflow: visible !important;
    }

    #messagesModal .modal-body .col-12.col-md-10 {
      overflow: visible !important;
    }

    /* Ensure message list table takes full width */
    #messagesModal .plg-msg-scroll-container .email-list {
      width: 100% !important;
    }

    #messagesModal .plg-msg-scroll-container table.email-list {
      table-layout: fixed !important;
      width: 100% !important;
      min-width: 100% !important;
    }

    /* Column width distribution: Icon(3%) / From(15%) / Subject(47%) / Date(20%) / Delete(15%) */
    #messagesModal table.email-list th:nth-child(1),
    #messagesModal table.email-list td:nth-child(1) {
      width: 5% !important;
    }

    #messagesModal table.email-list th:nth-child(2),
    #messagesModal table.email-list td:nth-child(2) {
      width: 18% !important;
    }

    #messagesModal table.email-list th:nth-child(3),
    #messagesModal table.email-list td:nth-child(3) {
      width: 50% !important;
    }

    #messagesModal table.email-list th:nth-child(4),
    #messagesModal table.email-list td:nth-child(4) {
      width: 22% !important;
      white-space: nowrap !important;
    }

    #messagesModal table.email-list th:nth-child(5),
    #messagesModal table.email-list td:nth-child(5) {
      width: 5% !important;
    }

    #messagesModal .plg-msg-scroll-container {
      width: 100% !important;
    }

    /* Override any tighten classes */
    #messagesModal .tighten-left,
    #messagesModal .tighten-right {
      padding-left: 0.5rem !important;
      padding-right: 0.5rem !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
      max-width: 100% !important;
      flex: 0 0 83.333333% !important;
    }
  </style>
  <div class="modal fade messages-modal" id="messagesModal" tabindex="-1" aria-labelledby="messagesModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 90vw; min-width:90vw;">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="messagesModalLabel"></h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close Messages"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 col-md-2 email-navigation">
              <!-- Sidebar with category links -->
              <div class="card" style="min-height: 75vh;">
                <div class="card-header">
                  <h5>Categories</h5>
                </div>
                <div class="card-body filter-meeting-card-body" style="background-color:white;">
                  <div class="list-group list-group-flush">
                    <a href="#" data-category="all" class="list-group-item message-category close-message active d-flex align-items-center">
                      <i class='bx bxs-inbox me-3 font-20'></i><span>All</span>
                    </a>
                    <a href="#" data-category="1" class="list-group-item message-category close-message d-flex align-items-center">
                      <i class='bx bxs-alarm-snooze me-3 font-20'></i><span>Alerts</span>
                    </a>
                    <a href="#" data-category="2" class="list-group-item message-category close-message d-flex align-items-center">
                      <i class='bx bxs-send me-3 font-20'></i><span>Notifications</span>
                    </a>
                    <a href="#" data-category="3" class="list-group-item message-category close-message d-flex align-items-center">
                      <i class='bx bxs-star me-3 font-20'></i><span>Messages</span>
                    </a>
                  </div>
                  <hr>
                  <button type="button" class="btn btn-outline-secondary btn-sm w-100 markAllReadBtn">
                    <i class="fas fa-check-double me-1"></i> Mark All Read
                  </button>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-10 mb-2 tighten-left tighten-right">
              <!-- Search and Filter Bar -->
              <div class="card mb-2 plg-msg-search-bar">
                <div class="card-body py-2">
                  <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                      <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="msgSearchInput" placeholder="Search messages...">
                        <button class="btn btn-outline-secondary msgSearchClear" type="button" title="Clear search">
                          <i class="fas fa-times"></i>
                        </button>
                      </div>
                    </div>
                    <div class="col-6 col-md-2">
                      <input type="date" class="form-control form-control-sm" id="msgDateFrom" placeholder="From date">
                    </div>
                    <div class="col-6 col-md-2">
                      <input type="date" class="form-control form-control-sm" id="msgDateTo" placeholder="To date">
                    </div>
                    <div class="col-6 col-md-2">
                      <button class="btn btn-primary btn-sm w-100 msgSearchBtn">
                        <i class="fas fa-filter me-1"></i> Filter
                      </button>
                    </div>
                    <div class="col-6 col-md-2">
                      <button class="btn btn-outline-secondary btn-sm w-100 msgResetFilters">
                        <i class="fas fa-undo me-1"></i> Reset
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Content area for messages -->
              <div class="card plg-msg-scroll-container">
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
                      <p class="text-right text-end pull-right">
                        <button type="button" class="btn btn-secondary close-message btn-sm">Close Message</button>
                      </p>

                    </div>
                  </div>
                </div>


                <table class="table email-list">
                  <thead>
                    <tr>
                      <th></th>
                      <th>From</th>
                      <th>Subject</th>
                      <th>Date Sent

                      </th>
                      <th>
                        <input type="checkbox" id="checkAllMessageDelBoxes">
                        <button class="deleteChecked btn btn-outline-danger btn-sm ms-2 py-0">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                        
                          </th>
                    </tr>
                  </thead>
                  <tbody class="messagesHere">
                  </tbody>
                </table>
                <div class="text-center no-messages email-list">

                </div>
              </div>
              <!-- Pagination Controls -->
              <div class="card mt-2 plg-msg-pagination-bar email-list">
                <div class="card-body py-2">
                  <div class="row align-items-center">
                    <div class="col-12 col-md-4 text-muted small">
                      <span class="msgPaginationInfo">Showing 0 of 0 messages</span>
                    </div>
                    <div class="col-12 col-md-8">
                      <nav aria-label="Message pagination" class="float-md-end">
                        <ul class="pagination pagination-sm mb-0 msgPagination">
                          <li class="page-item disabled msgPrevPage">
                            <a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-left"></i></a>
                          </li>
                          <li class="page-item active">
                            <span class="page-link msgCurrentPage">1</span>
                          </li>
                          <li class="page-item disabled">
                            <span class="page-link">/</span>
                          </li>
                          <li class="page-item">
                            <span class="page-link msgTotalPages">1</span>
                          </li>
                          <li class="page-item disabled msgNextPage">
                            <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                          </li>
                        </ul>
                      </nav>
                    </div>
                  </div>
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

    <script>
      $(document).ready(function() {

        let deleted = [];

        // Pagination and search state
        let currentPage = 1;
        let perPage = 50;
        let totalPages = 1;
        let totalCount = 0;
        let currentSearch = '';
        let currentDateFrom = '';
        let currentDateTo = '';
        let currentCategory = 'all';

        // Initialize groupedMessages with PHP data for immediate availability
        // AJAX fetch will refresh this data when it completes
        let groupedMessages = groupMessagesByType(<?= json_encode($plgMessages) ?>);
        let allMessages = []; // Store all fetched messages for client-side filtering
        fetchMessages();
        // Initialize modal and category display
        $('#messagesModal').modal({
          show: false
        });

        // Function to group messages by msg_type
        function groupMessagesByType(notifications) {
          const groupedMessages = {
            1: [],
            2: [],
            3: []
          };

          notifications.forEach(function(message) {
            groupedMessages[message.msg_type].push(message);
          });

          return groupedMessages;
        }

        // Function to display messages based on the selected category
        function displayMessages(category) {
          console.log("displayMessages");
          // Use the global groupedMessages variable (initialized with PHP data, refreshed by AJAX)

          // Select the table body to populate with messages
          const tableBody = $('.messagesHere');
          tableBody.empty(); // Clear existing messages

          // Helper function to replace null with empty string or "System" and "Message"
          function replaceNullOrEmpty(value, replacement) {
            return value !== null ? value : replacement;
          }

          // Function to generate the HTML for a message row
          function generateMessageRow(message) {
            const fname = replaceNullOrEmpty(message.fname, 'System');
            const lname = replaceNullOrEmpty(message.lname, 'Message');

            return `
      <tr class="msg${message.id}">
        <td class="loadMessage" data-id="${message.id}">${getCategoryIcon(message.msg_type)}</td>
        <td class="loadMessage" data-id="${message.id}"><span class="${message.msg_read === 0 ? 'unread' : ''}" id="msg-${message.id}">${fname} ${lname}</span></td>
        <td class="loadMessage" data-id="${message.id}">${message.title}</td>
        <td class="loadMessage" data-id="${message.id}">${message.date}</td>
        <td><input class="messageDelBox" type="checkbox" data-id="${message.id}"></td>
      </tr>
    `;
          }

          // Set the modal title based on the selected category
          const modalTitle = $('.modal-title');
          let noMsg = "";
          switch (category) {

            case 'all':
              modalTitle.text('All Messages');
              noMsg = "";
              $('.no-messages').html("");
              break;
            case 1:

              modalTitle.text('Alerts');
              noMsg = "No alerts found.";
              break;
            case 2:

              modalTitle.text('Notifications');
              noMsg = "No notifications found.";
              break;
            case 3:

              modalTitle.text('Messages');
              noMsg = "No direct messages found.";
              break;
            default:
              console.log(category);
              modalTitle.text('');
              noMsg = "No messages found.";
              break;
          }

          // Check if the selected category is "all" and display all messages
          if (category === 'all') {
            const allMessages = [].concat(...Object.values(groupedMessages));

            // Filter out deleted messages from allMessages
            const filteredMessages = allMessages.filter(function(message) {
              return !deleted.includes(message.id);
            });

            filteredMessages.forEach(function(message) {
              const row = generateMessageRow(message);
              tableBody.append(row);
            });
          } else {
            const messages = groupedMessages[category];

            // Filter out deleted messages from messages
            const filteredMessages = messages.filter(function(message) {
              return !deleted.includes(message.id);
            });

            if (filteredMessages && filteredMessages.length === 0) {
              // Display a message when no messages are found
              $('.no-messages').html(`${noMsg}`);
            } else {
              // Iterate through the filtered messages and append them to the table
              filteredMessages.forEach(function(message) {
                const row = generateMessageRow(message);
                tableBody.append(row);
              });

              // Clear the "No messages found" message
              $('.no-messages').html("");
              $("#checkAllMessageDelBoxes").prop('checked', false);
            }
          }
        }


        // Helper function to get the icon based on message type
        function getCategoryIcon(msgType) {
          switch (msgType) {
            case 1:
              return '<i class="ms-2 fas fa-exclamation-circle text-danger" style="font-size: 1rem;"></i>';
            case 2:
              return '<i class="ms-2 fas fa-bell text-warning" style="font-size: 1rem;"></i>';
            case 3:
              return '<i class="ms-2 fas fa-envelope text-primary" style="font-size: 1rem;"></i>';
            default:
              return '';
          }
        }

        $('.message-category').click(function(e) {
          e.preventDefault();

          // Remove the 'active' class from all categories
          $('.message-category').removeClass('active');

          // Add the 'active' class to the clicked category
          $(this).addClass('active');

          const category = $(this).data('category');
          currentCategory = category; // Store current category

          // Update the modal title based on the selected category
          $('.modal-title').text(category === 'all' ? 'All Messages' : category);
          // Call the function to display messages for the selected category
          displayMessages(category);
        });

        //open modal on click
        //note that there is a .loadMessage class that can be used to open the bare message in a modal. Not ready for prime time, but it's there.

        $(document).on("click", ".openMessageButton", function() {
          const initialCategory = this.getAttribute('data-initial-category') || 'all';
          const messageId = this.getAttribute('data-message-id'); // Get the message ID

          // Reset filters and pagination when opening modal
          currentPage = 1;
          currentSearch = '';
          currentDateFrom = '';
          currentDateTo = '';
          $('#msgSearchInput').val('');
          $('#msgDateFrom').val('');
          $('#msgDateTo').val('');

          // Always fetch fresh messages when opening modal
          fetchMessages(1, '', '', '').then(function() {
            if (messageId) {
              console.log("loading by message id");
              loadSpecificMessage(messageId);
            } else {
              console.log("loading by category");
              currentCategory = initialCategory;
              $(`.message-category[data-category="${initialCategory}"]`).click();
              $('.email-content').hide();
              $('.email-list').show();
              $('.plg-msg-search-bar').show();
              $('.plg-msg-pagination-bar').show();
            }
          });

          $('#messagesModal').modal('show');
        });

        // Listen for new messages event from polling system
        $(document).on('plgMessaging:newMessages', function(e, data) {
          console.log("PLG Messaging: New messages detected, refreshing...");
          fetchMessages().then(function() {
            // Re-display current category if modal is open
            if ($('#messagesModal').hasClass('show')) {
              const activeCategory = $('.message-category.active').data('category') || 'all';
              displayMessages(activeCategory);
            }
          });
        });

        //hide the single message and show all messages
        $(document).on("click", ".close-message", function() {
          $('.email-list').show();
          $('.email-content').hide();
          $('.plg-msg-search-bar').show();
          $('.plg-msg-pagination-bar').show();
        });

        $(document).on("click", ".loadMessage", function() {
          console.log("loadMessage Clicked");

          $(this).removeClass("unread");
          var messageId = $(this).data("id");
          console.log(messageId);
          loadSpecificMessage(messageId);
        });


        // Fetch messages via AJAX - returns Promise for chaining
        function fetchMessages(page = 1, search = '', dateFrom = '', dateTo = '', msgType = null) {
          var formData = {
            'csrf': '<?= Token::generate(); ?>',
            'page': page,
            'limit': perPage,
            'search': search,
            'date_from': dateFrom,
            'date_to': dateTo
          };

          // Only add msg_type filter if searching/filtering server-side
          if (msgType !== null && msgType !== 'all') {
            formData.msg_type = msgType;
          }

          return $.ajax({
              type: 'POST',
              url: '<?= $us_url_root ?>usersc/plugins/messaging/assets/parsers/fetchMessages.php',
              data: formData,
              dataType: 'json',
            })
            .done(function(data) {
              console.log("fetchMessages response:", data);
              if (data.success == true) {
                allMessages = data.messages;
                groupedMessages = groupMessagesByType(data.messages);

                // Update pagination state
                if (data.pagination) {
                  currentPage = data.pagination.current_page;
                  totalPages = data.pagination.total_pages;
                  totalCount = data.pagination.total_count;
                  updatePaginationUI();
                }

                // Update badge counts
                if (data.counts) {
                  $(".type-1").html(data.counts.alert_count);
                  $(".type-2").html(data.counts.notification_count);
                  $(".type-3").html(data.counts.message_count);
                }
              }
            });
        }

        // Update pagination UI elements
        function updatePaginationUI() {
          $('.msgCurrentPage').text(currentPage);
          $('.msgTotalPages').text(totalPages || 1);

          var start = totalCount > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
          var end = Math.min(currentPage * perPage, totalCount);
          $('.msgPaginationInfo').text('Showing ' + start + '-' + end + ' of ' + totalCount + ' messages');

          // Enable/disable prev/next buttons
          if (currentPage <= 1) {
            $('.msgPrevPage').addClass('disabled');
          } else {
            $('.msgPrevPage').removeClass('disabled');
          }

          if (currentPage >= totalPages) {
            $('.msgNextPage').addClass('disabled');
          } else {
            $('.msgNextPage').removeClass('disabled');
          }
        }

        // Search button click
        $(document).on('click', '.msgSearchBtn', function() {
          currentSearch = $('#msgSearchInput').val().trim();
          currentDateFrom = $('#msgDateFrom').val();
          currentDateTo = $('#msgDateTo').val();
          currentPage = 1;
          fetchMessages(currentPage, currentSearch, currentDateFrom, currentDateTo).then(function() {
            displayMessages(currentCategory);
          });
        });

        // Search on Enter key
        $('#msgSearchInput').on('keypress', function(e) {
          if (e.which === 13) {
            e.preventDefault();
            $('.msgSearchBtn').click();
          }
        });

        // Clear search
        $(document).on('click', '.msgSearchClear', function() {
          $('#msgSearchInput').val('');
          currentSearch = '';
          currentPage = 1;
          fetchMessages(currentPage, currentSearch, currentDateFrom, currentDateTo).then(function() {
            displayMessages(currentCategory);
          });
        });

        // Reset all filters
        $(document).on('click', '.msgResetFilters', function() {
          $('#msgSearchInput').val('');
          $('#msgDateFrom').val('');
          $('#msgDateTo').val('');
          currentSearch = '';
          currentDateFrom = '';
          currentDateTo = '';
          currentPage = 1;
          fetchMessages(currentPage, '', '', '').then(function() {
            displayMessages(currentCategory);
          });
        });

        // Pagination - Previous page
        $(document).on('click', '.msgPrevPage a', function(e) {
          e.preventDefault();
          if (currentPage > 1) {
            currentPage--;
            fetchMessages(currentPage, currentSearch, currentDateFrom, currentDateTo).then(function() {
              displayMessages(currentCategory);
            });
          }
        });

        // Pagination - Next page
        $(document).on('click', '.msgNextPage a', function(e) {
          e.preventDefault();
          if (currentPage < totalPages) {
            currentPage++;
            fetchMessages(currentPage, currentSearch, currentDateFrom, currentDateTo).then(function() {
              displayMessages(currentCategory);
            });
          }
        });

        // Mark All as Read
        $(document).on('click', '.markAllReadBtn', function() {
          var btn = $(this);
          btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Marking...');

          var msgType = currentCategory !== 'all' ? currentCategory : null;

          $.ajax({
            type: 'POST',
            url: '<?= $us_url_root ?>usersc/plugins/messaging/assets/parsers/markAllRead.php',
            data: {
              'csrf': '<?= Token::generate(); ?>',
              'msg_type': msgType
            },
            dataType: 'json'
          })
          .done(function(data) {
            if (data.success) {
              // Update badge counts
              $(".type-1").html(data.alert_count);
              $(".type-2").html(data.notification_count);
              $(".type-3").html(data.message_count);

              // Refresh message list
              fetchMessages(currentPage, currentSearch, currentDateFrom, currentDateTo).then(function() {
                displayMessages(currentCategory);
              });

              // Show success feedback
              if (typeof usSuccess === 'function') {
                usSuccess('All messages marked as read');
              }
            }
          })
          .always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Mark All Read');
          });
        })

        function loadSpecificMessage(messageId) {
          var formData = {
            'id': messageId, // Use messageId instead of id
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
                $("#id").val(messageId); // Use messageId instead of id

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

                // Set other message details
                $('.email-content').show();
                $('.email-list').hide();
                $('.plg-msg-search-bar').hide();
                $('.plg-msg-pagination-bar').hide();

                $("#messageModalBody").html(data.msg.message);
                console.log(data.msg.from_name);
                $("#messageFrom").html(data.msg.from_name);
                $("#messageDate").html(data.msg.date);
                $("#messageBody").html(data.msg.message);
                $("#msg-" + messageId).removeClass("unread"); // Use messageId instead of id
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
                    const messageId = checked[i];
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


      });
    </script>
  <?php
}
  ?>