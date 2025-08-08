<div class="modal fade messages-modal" id="messagesModal" tabindex="-1" aria-labelledby="messagesModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 90vw;">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="messagesModalLabel"></h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close Messages"></button>
      </div>
<div class="modal-body">
        <div class="row">
          <div class="col-2 email-navigation">
            <!-- Sidebar with category links -->
            <div class="card" style="min-height: 75vh;">
              <div class="card-header">
                <h5>Categories</h5>
                <div class="input-group input-group-sm">
                </div>
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
              </div>
            </div>
          </div>
          <div class="col-10 mt-2 mb-2 tighten-left tighten-right">
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
                    <p class="text-right text-end pull-right">
                      <button type="button" class="btn btn-secondary close-message btn-sm">Close Message</button>
                    </p>

                  </div>
                </div>
              </div>


              <table class="table omt-table paginate email-list">
                <thead>
                  <tr>
                    <th></th>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Date Sent</th>
                    <th>
                      <input type="checkbox" id="checkAllMessageDelBoxes">
                      <button class="deleteChecked btn btn-outline-danger btn-sm ms-3 py-0">Delete Checked</button>

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
