var BcbpDatatable = React.createClass({

    getInitialState: function () {
        return {
            showEditModal: false,
            showReleaseModal: false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            user: null,
            filters: {
                electId: null,
                provinceCode: null,
                proId: null
            }
        }
    },

    componentDidMount: function () {
        //this.loadUser(window.userId);
        this.initDatatable();
    },

    loadUser: function (userId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_user", { id: userId }),
            type: "GET"
        }).done(function (res) {
            self.setState({ user: res }, self.reinitSelect2);
        });
    },


    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var bcbp_table = $("#bcbp_table");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_bcbp_temp_profile", {}, true);

        grid_project_event.init({
            src: bcbp_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "pageLength" : 100,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.name = $('#bcbp_table input[name="name"]').val();
                        d.position = $('#bcbp_table input[name="position"]').val();
                        d.groupName = $('#bcbp_table input[name="group_name"]').val();
                        d.unitName = $('#bcbp_table input[name="unit_name"]').val();
                        d.chapterName = $('#bcbp_table input[name="chapter_name"]').val();
                        d.batchName = $('#bcbp_table input[name="batch_name"]').val();
                        d.contactNumber = $('#bcbp_table input[name="contact_number"]').val();
                        d.gender = $('#bcbp_table input[name="gender"]').val();
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
                }, {
                    'className': 'align-center',
                    'targets': [0, 3]
                }],
                "order": [
                    [0, "desc"]
                ],
                "columns": [
                    {
                        "data": null,
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, full, meta) {
                            return meta.settings._iDisplayStart + meta.row + 1;
                        }
                    },
                    {
                        "data": "name",
                        "className": "text-left",
                        "render": function (data) {
                            return "<a href='javascript:void(0);' class='release-button'><strong>" + data + '</strong></a>';
                        }
                    },
                    {
                        "data": "nickname",
                        "className": "text-center",
                        "width": 50,
                    },
                    {
                        "data": "position",
                        "className": "text-center",
                        "width": 100,
                    },
                    {
                        "data": "unit_name",
                        "className": "text-center",
                        "width": 100,
                    },
                    {
                        "data": "group_name",
                        "className": "text-center",
                        "width": 100,
                    },
                    {
                        "data": "batch_name",
                        "width": 100,
                        "className": "text-center"
                    },
                    {
                        "data": "chapter_name",
                        "className": "text-center",
                        "width": 150
                    },
                    {
                        "data": "contact_number",
                        "width": 100,
                        "className": "text-center"
                    },
                    {
                        "data": "gender",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "birthdate",
                        "className": "text-center",
                        "width": 100
                    },

                    {
                        "data": "source_number",
                        "className": "text-center",
                        "width": 50
                    },

                    {
                        "data": "created_at",
                        "className": "text-center",
                        "width": 150
                    },

                    {
                        "width": 50 ,
                        "render": function (data, type, row) {

                            var viewBtn = '<button class="btn btn-xs default edit2-btn"><i class="fa fa-eye"></i></button>';
                            var editBtn = '<button class="btn btn-xs green edit-btn"><i class="fa fa-edit"></i></button>';
                            var assignBtn = '<button class="btn btn-xs blue assign-btn"><i class="fa fa-edit"></i>Assign</button>';
                            var deleteBtn = '<button class="btn btn-xs red-sunglo delete-btn"><i class="fa fa-trash"></i></button>';
                            var btnGroup = '';

                            btnGroup += editBtn;

                            return btnGroup;
                        },

                        "className": "text-center"
                    }
                ],
            }
        });

        bcbp_table.on('click', '.edit-btn', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.id });
        });

        self.grid = grid_project_event;
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
        this.reload();
    },

    closeReleaseModal: function () {
        this.setState({ showReleaseModal: false, target: null });
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    openClosingModal: function () {
        this.setState({ showClosingModal: true });
    },

    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to delete this request?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_tupad_transaction", { id: id }),
                type: "DELETE"
            }).done(function (res) {
                self.reload();
            });
        }
    },

    handleFilterChange: function () {
        var self = this;
        clearTimeout(this.state.typingTimer);
        this.state.typingTimer = setTimeout(function () {
            self.reload();
        }, this.state.doneTypingInterval);
    },

    reload: function () {

        console.log("relaod databable");
        if (this.grid != null) {
            this.grid.getDataTable().ajax.reload();
        }
    },

    openProfile: function (id) {
        this.setState({ showEditModal: true, target: id });
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    render: function () {
        return (
            <div>

                {
                    this.state.showEditModal &&
                    <BcbpEditModal
                        show={this.state.showEditModal}
                        reload={this.reload}
                        onHide={this.closeEditModal}
                        id={this.state.target}
                    />
                }
                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="bcbp_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Nickname</th>
                                <th>Position</th>
                                <th>Unit</th>
                                <th>Action Group</th>
                                <th>Batch #</th>
                                <th>Chapter</th>
                                <th>Contact Number</th>
                                <th>Gender</th>
                                <th>Birthdate</th>
                                <th>Sender</th>
                                <th>Date Created</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="name" onChange={this.handleFilterChange} />
                                </td>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="position" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="unit_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="group_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="batch_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="chapter_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="contact_number" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="gender" onChange={this.handleFilterChange} />
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
});

window.BcbpDatatable = BcbpDatatable;