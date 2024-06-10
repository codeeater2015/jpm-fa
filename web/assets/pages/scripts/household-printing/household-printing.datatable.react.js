var HouseholdPrintingDatatable = React.createClass({

    getInitialState: function () {
        return {
            householdData: []
        }
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;

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

        $("#household_table #municipality_select2").on("change", function () {
            self.loadData();
        });

        $("#household_table #barangay_select2").on("change", function () {
            self.loadData();
        });

    },

    loadData: function () {

        let url = Routing.generate("ajax_get_table_household_headers", {
            municipalityName: $("#household_table #municipality_select2").val(),
            barangayName: $("#household_table #barangay_select2").val()
        }
        );

        var self = this;
        self.requestHierarchyData = $.ajax({
            url: url,
            type: "GET"
        }).done(function (res) {
            console.log("household data has been received");
            self.setState({ householdData: res });
        });
    },

    printPage: function () {
        console.log('printing');
        $.print("#household_table" /*, options*/);
    },

    render: function () {
        let counter = 0;

        return (
            <div>
                <button type="button" className="btn btn-primary" onClick={this.printPage}>Print Page</button>

                <div id="table-container" className="table-container" style={{ marginTop: "20px" }}>
                    <br />
                    <br />
                    <table id="household_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th rowSpan="2">No</th>
                                <th rowSpan="2">Name</th>
                                <th rowSpan="2">Municipality</th>
                                <th rowSpan="2" className="text-center">Barangay</th>
                                <th rowSpan="2" className="text-center">House No.</th>
                                <th style={{ textAlign: "center" }} className="text-center" colSpan="3">Household</th>
                                <th rowSpan="2" className="text-center">Contact #</th>
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
                            </tr>
                        </thead>
                        <tbody>
                            {this.state.householdData.map(function (item, index) {
                                counter += 1;
                                return (
                                    <tr>
                                        <td style={{ textAlign: "center" }} className="text-center">{counter}</td>
                                        <td style={{ textAlign: "center" }} className="text-center"> {item.voter_name}</td>
                                        <td style={{ textAlign: "center" }} className="text-center">{item.municipality_name}</td>
                                        <td style={{ textAlign: "center" }} className="text-center">{item.barangay_name}</td>
                                        <td style={{ textAlign: "center" }} className="text-center">{item.household_code}</td>
                                        <td style={{ textAlign: "center" }} className="text-center">{item.total_voters}</td>
                                        <td style={{ textAlign: "center" }} className="text-center">{item.total_non_voters}</td>
                                        <td style={{ textAlign: "center" }} className="text-center">{item.total_members}</td>
                                        <td style={{ textAlign: "center" }} className="text-center">{item.contact_no}</td>
                                    </tr>
                                )
                            })}
                            {this.state.householdData.length == 0 ?
                                <tr>
                                    <td colSpan="9" className="text-center">No records was found...</td>
                                </tr>
                                : ""
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
});

window.HouseholdPrintingDatatable = HouseholdPrintingDatatable;