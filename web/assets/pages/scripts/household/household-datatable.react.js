var HouseholdDatatable = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showEditModal: false,
            showRecruitsModal: false,
            showHouseholdSummary: false,
            target: null,
            typingTimer: null,
            municipalityNo: null,
            modalMunicipalityNo : null,
            doneTypingInterval: 1500,
            user: null,
            summary: null,
            filters: {
                electId: 3,
                provinceCode: 53,
                proId: 3
            }
        }
    },

    componentDidMount: function () {
        this.loadUser(window.userId);
        this.initSelect2();
        this.loadSummary();
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

    loadSummary: function (userId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_m_get_household_voters_summary"),
            type: "GET"
        }).done(function (res) {
            console.log("summary has been received");
            self.setState({ summary: res });
        });
    },

    initSelect2: function () {
        var self = this;

        $("#handler_component #election_select2").select2({
            casesentitive: false,
            placeholder: "Select Election...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_elections'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.elect_id, text: item.elect_name };
                        })
                    };
                },
            }
        });

        $("#handler_component #project_select2").select2({
            casesentitive: false,
            placeholder: "Select Project...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_projects'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.pro_id, text: item.pro_name };
                        })
                    };
                },
            }
        });

        $("#handler_component #province_select2").select2({
            casesentitive: false,
            placeholder: "Enter Province...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_province_strict'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.province_code, text: item.name };
                        })
                    };
                },
            }
        });


        $("#household_table #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.municipality_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#household_table #barangay_select2").select2({
            casesentitive: false,
            placeholder: "Enter name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: 53,
                        municipalityNo: $("#household_table #municipality_select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.brgy_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#handler_component #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();

            self.setState({ filters: filters }, self.reload);
        });

        $("#handler_component #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });

        $("#handler_component #province_select2").on("change", function () {
            var filters = self.state.filters;
            filters.provinceCode = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });

        $("#household_table #municipality_select2").on("change", function () {
            self.handleFilterChange();
            self.setState({ municipalityNo: $(this).val() });
        });

        $("#household_table #barangay_select2").on("change", function () {
            self.handleFilterChange();
        });

    },

    reinitSelect2: function () {
        var self = this;

        if (!self.isEmpty(self.state.user.project)) {
            var provinceCode = self.state.user.project.provinceCode;

            self.requestProvince = $.ajax({
                url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
                type: "GET"
            }).done(function (res) {
                $("#handler_component #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {
                $("#handler_component #project_select2").empty()
                    .append($("<option/>")
                        .val(res.proId)
                        .text(res.proName))
                    .trigger("change");

                self.initDatatable();
            });
        }

        self.requestActiveElection = $.ajax({
            url: Routing.generate("ajax_get_active_election"),
            type: "GET"
        }).done(function (res) {
            $("#handler_component #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin) {
            $("#handler_component #election_select2").attr('disabled', 'disabled');
            $("#handler_component #province_select2").attr('disabled', 'disabled');
            $("#handler_component #project_select2").attr('disabled', 'disabled');
        }
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var household_table = $("#household_table");
        var grid_project_recruitment = new Datatable();
        var url = Routing.generate("ajax_get_datatable_household_header", {}, true);

        grid_project_recruitment.init({
            src: household_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "pageLength": 100,
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.voterName = $('#household_table input[name="voter_name"]').val();
                        d.voterGroup = $('#household_table input[name="voter_group"]').val();
                        d.municipalityNo = $('#household_table #municipality_select2').val();
                        d.barangayNo = $('#household_table #barangay_select2').val();
                        d.householdCode = $('#household_table input[name="household_code"]').val();
                        d.electId = 423;
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 6, 7, 8, 9, 10, 11, 12]
                }, {
                    'className': 'align-center',
                    'targets': [2, 3]
                }],
                "order": [
                    [1, "asc"]
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
                        "data": "voter_name",
                        "render": function (data, type, row) {
                            return row.is_non_voter == 1 ? '--- ' + data : data;
                        }
                    },
                    { "data": "voter_group", "className": "text-center", width: 40 },
                    { "data": "municipality_name", "className": "text-center", width: 150 },
                    { "data": "barangay_name", width: 120 },
                    { "data": "household_code", width: 80, className: "text-center" },
                    {
                        "data": "total_voters",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "total_non_voters",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "total_members",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "data": "contact_no",
                        "className": "text-center",
                        "width": 100
                    },
                    {
                        "data": "updated_at",
                        "className": "text-center",
                        "width": 80,
                        "render": function (data, type, row) {
                            console.log('updated at');
                            console.log(data);

                            return (data == "" || data == null) ? "" : moment(data).format("MMM Do YY");
                        }
                    },
                    {
                        "data": "updated_by",
                        "className": "text-center",
                        "width": 50
                    },
                    {
                        "width": 100,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var recruitBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green recruits-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-calendar'></i></a>";
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-primary edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit'></i></a>";
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return editBtn + recruitBtn + deleteBtn;
                        }
                    }
                ],
            }
        });

        household_table.on('click', '.edit-button', function () {
            var data = grid_project_recruitment.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.id });
        })

        household_table.on('click', '.recruits-button', function () {
            var data = grid_project_recruitment.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showRecruitsModal: true, target: data.id });
        });

        household_table.on('click', '.delete-button', function () {
            var data = grid_project_recruitment.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_project_recruitment;
    },


    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false, target: null });
    },

    openEditModal: function () {
        this.setState({ showEditModal: true });
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
    },

    closeRecruitsModal: function () {
        this.setState({ showRecruitsModal: false, target: null });
    },

    delete: function (householdId) {
        var self = this;

        if (confirm("Are you sure you want to delete this record ?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_household_header", { householdId: householdId }),
                type: "DELETE"
            }).done(function (res) {
                self.reload();
            });
        }
    },

    openHouseholdSummary : function(municipalityNo){
        console.log("modal municipalityNo ", municipalityNo);

        this.setState({ showHouseholdSummary : true , modalMunicipalityNo : municipalityNo });
    },

    closeHouseholdSummary : function(){
        this.setState( { showHouseholdSummary : false});
    },

    onCreateSuccess: function (id) {
        var self = this;
        self.setState({ showRecruitsModal: true, target: id });
    },

    handleFilterChange: function () {
        var self = this;

        clearTimeout(this.state.typingTimer);
        this.state.typingTimer = setTimeout(function () {
            self.reload();
        }, this.state.doneTypingInterval);
    },

    reload: function () {
        if (this.grid != null) {
            this.grid.getDataTable().ajax.reload();
        }
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    render: function () {
        let summary = this.state.summary;
        let self = this;

        return (
            <div>
                {
                    this.state.showCreateModal &&
                    <HouseholdCreateModal
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                        provinceCode={this.state.filters.provinceCode}
                        show={this.state.showCreateModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeCreateModal}
                        onSuccess={this.onCreateSuccess}
                    />
                }

                {
                    this.state.showEditModal &&
                    <HouseholdEditModal
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                        provinceCode={this.state.filters.provinceCode}
                        show={this.state.showEditModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeEditModal}
                        householdId={this.state.target}
                    />
                }
                {
                    this.state.showRecruitsModal &&
                    <HouseholdMemberModal
                        id={this.state.target}
                        show={this.state.showRecruitsModal}
                        reload={this.reload}
                        onHide={this.closeRecruitsModal}
                        proId={this.state.filters.proId}
                        electId={this.state.filters.electId}
                        notify={this.props.notify}
                        onDataPatched={this.reload}
                    />
                }

                {
                    this.state.showHouseholdSummary &&
                    <HouseholdSummaryModal
                        show={this.state.showHouseholdSummary}
                        municipalityNo={this.state.modalMunicipalityNo}
                        onHide={this.closeHouseholdSummary}
                    />
                }

                <div className="row" id="handler_component">

                    <div className="col-md-6 col-md-offset-3" >
                        <table className="table table-condensed table-bordered">
                            <thead style={{ backgroundColor: "#5ab866" }}>
                                <tr className="text-center">
                                    <th rowSpan="2" className="text-center">Household Address</th>
                                    <th rowSpan="2" className="text-center">Households</th>
                                    <th rowSpan="2" className="text-center">Est. Target</th>
                                    <th colSpan="2" className="text-center">Voting Address</th>
                                    <th rowSpan="2" className="text-center">Outside</th>
                                    <th rowSpan="2" className="text-center">Potential</th>
                                    <th rowSpan="2" className="text-center">Total</th>
                                    <th rowSpan="2" className="text-center"></th>
                                </tr>
                                <tr>
                                    <th className="text-center">Puerto</th>
                                    <th className="text-center">Aborlan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {(summary != null && (self.state.municipalityNo == null || self.state.municipalityNo == '01')) ? (
                                    <tr className="text-center">
                                        <td>ABORLAN</td>
                                        <td>{summary.household[0].total_household}</td>
                                        <td>{summary.household[0].total_household * 4}</td>
                                        <td>{summary.voters[0].total_puerto}</td>
                                        <td>{summary.voters[0].total_aborlan}</td>
                                        <td>{summary.total_voter_outside[0].total_voter}</td>
                                        <td>{summary.total_voter_potential[0].total_voter_potential}</td>
                                        <td>{Number.parseInt(summary.voters[0].total_aborlan) + Number.parseInt(summary.voters[0].total_puerto) + Number.parseInt(summary.total_voter_potential[0].total_voter_potential) }</td>
                                        <td>
                                            <button onClick={this.openHouseholdSummary.bind(this,'01')} className="btn btn-xs btn-primary btn-icon"><i className="fa fa-eye"></i></button>
                                        </td>
                                    </tr>
                                ) : null}
                                {(summary != null && (self.state.municipalityNo == null || self.state.municipalityNo == '16')) ? (
                                    <tr className="text-center">
                                        <td>PUERTO PRINCESA CITY</td>
                                        <td>{summary.household[1].total_household}</td>
                                        <td>{summary.household[1].total_household * 4}</td>
                                        <td>{summary.voters[1].total_puerto}</td>
                                        <td>{summary.voters[1].total_aborlan}</td>
                                        <td>{summary.total_voter_outside[1].total_voter}</td>
                                        <td>{summary.total_voter_potential[1].total_voter_potential}</td>
                                        <td>{Number.parseInt(summary.voters[1].total_aborlan) + Number.parseInt(summary.voters[1].total_puerto) + Number.parseInt(summary.total_voter_potential[1].total_voter_potential) }</td>
                                        
                                        <td>
                                            <button onClick={this.openHouseholdSummary.bind(this,'16')}  className="btn btn-xs btn-primary btn-icon"><i className="fa fa-eye"></i></button>
                                        </td>
                                    </tr>
                                ) : null}
                                {(summary != null && (self.state.municipalityNo == null)) ? (
                                    <tr className="text-center">
                                        <td><strong>Total</strong></td>
                                        <td>{ Number.parseInt(summary.household[0].total_household) + Number.parseInt(summary.household[1].total_household)}</td>
                                        <td>{(Number.parseInt(summary.household[0].total_household) + Number.parseInt(summary.household[1].total_household)) * 4}</td>
                                        <td>{Number.parseInt(summary.voters[0].total_puerto) + Number.parseInt(summary.voters[1].total_puerto)}</td>
                                        <td>{Number.parseInt(summary.voters[0].total_aborlan) + Number.parseInt(summary.voters[1].total_aborlan)}</td>
                                        <td>{Number.parseInt(summary.total_voter_outside[0].total_voter) + Number.parseInt(summary.total_voter_outside[1].total_voter)}</td>
                                        <td>{Number.parseInt(summary.total_voter_potential[0].total_voter_potential) + Number.parseInt(summary.total_voter_potential[1].total_voter_potential)}</td>
                                        <td>{(Number.parseInt(summary.voters[0].total_aborlan) + Number.parseInt(summary.voters[0].total_puerto) + Number.parseInt(summary.total_voter_potential[0].total_voter_potential) ) 
                                            + Number.parseInt(summary.voters[1].total_aborlan) + Number.parseInt(summary.voters[1].total_puerto) + Number.parseInt(summary.total_voter_potential[1].total_voter_potential) }</td>
                                        
                                        <td>
                                            
                                        </td>
                                    </tr>
                                ) : null}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div className="col-md-6">
                    <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>New Household</button>
                    <br />
                    <br />
                </div>
                <div className="col-md-12">
                    <div className="table-container" >
                        <table id="household_table" className="table table-striped table-bordered" width="100%">
                            <thead>
                                <tr >
                                    <th rowSpan="2">No</th>
                                    <th rowSpan="2">Name</th>
                                    <th rowSpan="2">Position</th>
                                    <th rowSpan="2" className="text-center">Municipality</th>
                                    <th rowSpan="2" className="text-center">Barangay</th>
                                    <th rowSpan="2" className="text-center">House No.</th>
                                    <th className="text-center" colSpan="3">Household</th>
                                    <th rowSpan="2" className="text-center">Contact #</th>
                                    <th rowSpan="2" className="text-center">Last Update</th>
                                    <th rowSpan="2" className="text-center">User</th>
                                    <th rowSpan="2" width="60px" className="text-center"></th>
                                </tr>
                                <tr>
                                    <th className="text-center">Voter</th>
                                    <th className="text-center">Non-Voter</th>
                                    <th className="text-center">Total</th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td style={{ padding: "10px 5px" }}>
                                        <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilterChange} />
                                    </td>
                                    <td>
                                        <input type="text" className="form-control form-filter input-sm" name="voter_group" onChange={this.handleFilterChange} />
                                    </td>
                                    <td style={{ padding: "10px 5px" }}>
                                        <select id="municipality_select2" className="form-control form-filter input-sm" >
                                        </select>
                                    </td>
                                    <td style={{ padding: "10px 5px" }}>
                                        <select id="barangay_select2" className="form-control form-filter input-sm">
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" className="form-control form-filter input-sm" name="household_code" onChange={this.handleFilterChange} />
                                    </td>
                                    <td></td>
                                    <td></td>
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
            </div>
        )
    }
});

window.HouseholdDatatable = HouseholdDatatable;