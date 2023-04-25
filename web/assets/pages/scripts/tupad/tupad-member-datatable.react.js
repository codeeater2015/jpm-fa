var TupadMemberDatatable = React.createClass({

    getInitialState: function () {
        return {
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
        this.loadUser(window.userId);
        this.initDatatable();
    },

    loadUser: function (userId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_user", { id: userId }),
            type: "GET"
        }).done(function (res) {
            self.setState({ user: res });
        });
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var tupad_detail_table = $("#tupad_detail_table");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_tupad_transaction_detail", {}, true);

        grid_project_event.init({
            src: tupad_detail_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.hdrId = self.props.hdrId;
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6]
                }, {
                    'className': 'align-center',
                    'targets': [0]
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
                        "data": "b_name",
                        "className": "text-left"
                    },
                    {
                        "data": "b_municipality",
                        "width": 150,
                        "className": "text-center"
                    },
                    {
                        "data": "b_barangay",
                        "className": "text-center",
                        "width": 180
                    },
                    {
                        "data": "is_voter",
                        "className": "text-center",
                        "width": 50,
                        "render": function (data) {
                            return parseInt(data) == 1 ? "YES" : "NO";
                        }
                    },
                    {
                        "data": "b_cellphone_no",
                        "className": "text-center",
                        "width": 50
                    },

                    {
                        "width": 50,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return deleteBtn;
                        }
                    }
                ],
            }
        });

        tupad_detail_table.on('click', '.release-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.id });
        });

        tupad_detail_table.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_project_event;
    },


    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to delete this request?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_tupad_transaction_detail", { id: id }),
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

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    render: function () {
        return (
            <div>
                
                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="tupad_detail_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Reg. Municipality</th>
                                <th>Reg. Barangay</th>
                                <th>Is Voter</th>
                                <th>Cellphone</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="beneficiary_name" onChange={this.handleFilterChange} />
                                </td>
                                <td></td>
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

window.TupadMemberDatatable = TupadMemberDatatable;