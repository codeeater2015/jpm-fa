var TupadSummaryDetailDatatable = React.createClass({

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

    componentDidMount: function () { },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var tupad_table = $("#tupad_table");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_tupad_transactions", {}, true);

        grid_project_event.init({
            src: tupad_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        //d.bName = $('#tupad_table input[name="beneficiary_name"]');
                        //d.bMunicipality = $('#tupad_table input[name="b_municipality"]');
                        //d.bBarangay = $('#tupad_table input[name="b_barangay"]');
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7, 8]
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
                        "data": "b_name",
                        "className": "text-left"
                    },
                    {
                        "data": "service_type",
                        "className": "text-center",
                        "width": 150,
                    },
                    {
                        "data": "source_municipality",
                        "width": 150,
                        "className": "text-center"
                    },
                    {
                        "data": "source_barangay",
                        "className": "text-center",
                        "width": 180
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

        tupad_table.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_project_event;
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

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    render: function () {
        return (
            <div>
                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="tupad_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Beneficiary Name</th>
                                <th>Asst. Municipality</th>
                                <th>Asst. Barangay</th>
                                <th>Reg. Municipality</th>
                                <th>Reg. Barangay</th>
                                <th>Is Voter</th>
                                <th width="50px"></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="beneficiary_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="source_municipality" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="source_barangay" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="b_municipality" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="b_barangay" onChange={this.handleFilterChange} />
                                </td>
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

window.TupadSummaryDetailDatatable = TupadSummaryDetailDatatable;