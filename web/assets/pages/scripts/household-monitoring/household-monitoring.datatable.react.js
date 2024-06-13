var HouseholdMonitoringTable = React.createClass({

    getInitialState: function () {
        return {
            householdData: [],
            householdData2: []
        }
    },

    componentDidMount: function () {
        this.loadData();
    },



    loadData: function () {


        let url = Routing.generate("ajax_get_table_household_monitoring_by_barangay");

        var self = this;
        self.requestHierarchyData = $.ajax({
            url: url,
            type: "GET"
        }).done(function (res) {
            console.log("household data has been received");
            self.setState({ householdData: res });
        });

        
        let url2 = Routing.generate("ajax_get_table_household_monitoring_by_date");

        var self = this;
        self.requestHierarchyData = $.ajax({
            url: url2,
            type: "GET"
        }).done(function (res) {
            console.log("household data has been received");
            self.setState({ householdData2: res });
        });
    },

    printPage: function () {
        console.log('printing');
        $.print("#household_table" /*, options*/);
    },

    render: function () {
        let counter = 0;
        let counter2 =  0;

        return (
            <div>
                <div className="row">

                <div className="col-md-6">
                        <h4><strong>Monitoring by Date</strong></h4>
                        <div id="table-container" className="table-container" style={{ marginTop: "20px" }}>
                            <table id="household_table2" className="table table-striped table-bordered" width="100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th className="text-center">Call Date</th>
                                        <th style={{ textAlign: "center" }} className="text-center" >Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {this.state.householdData2.map(function (item, index) {
                                        counter2 += 1;
                                        return (
                                            <tr>
                                                <td style={{ textAlign: "center" }} className="text-center">{counter2}</td>
                                                <td style={{ textAlign: "center" }} className="text-center">{item.call_date}</td>
                                                <td style={{ textAlign: "center" }} className="text-center">{item.total_household}</td>
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

                    <div className="col-md-6">
                        <h4><strong>Monitoring by Barangay</strong></h4>
                        <div id="table-container" className="table-container" style={{ marginTop: "20px" }}>
                            <table id="household_table" className="table table-striped table-bordered" width="100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Municipality</th>
                                        <th className="text-center">Barangay</th>
                                        <th style={{ textAlign: "center" }} className="text-center" >Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {this.state.householdData.map(function (item, index) {
                                        counter += 1;
                                        return (
                                            <tr>
                                                <td style={{ textAlign: "center" }} className="text-center">{counter}</td>
                                                <td style={{ textAlign: "center" }} className="text-center"> {item.municipality_name}</td>
                                                <td style={{ textAlign: "center" }} className="text-center">{item.barangay_name}</td>
                                                <td style={{ textAlign: "center" }} className="text-center">{item.total_household}</td>
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
                    

                </div>
            </div>
        )
    }
});

window.HouseholdMonitoringTable = HouseholdMonitoringTable;