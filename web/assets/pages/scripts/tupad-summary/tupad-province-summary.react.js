var TupadProvinceSummary = React.createClass({

    getInitialState: function () {
        return {
            records: []
        }
    },

    componentDidMount: function () {
        this.loadData();
    },

    loadData: function () {
        var self = this;

        self.requestMunicipality = $.ajax({
            url: Routing.generate("ajax_get_assistance_summary"),
            type: "GET"
        }).done(function (res) {
            console.log("data has been received");
            console.log(res);
            self.setState({ records: res });
        }).fail(function () {
            self.setState({ records: [] });
        });
    },


    notify: function (message, color) {
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    numberWithCommas: function (x) {
        if (x != 0) {
            var parts = x.toString().split(".");
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return parts.join(".");
        } else {
            return "";
        }
    },

    render: function () {
        var self = this;

        var gDisplaced = 0;
        var gDisplacedDuplicates = 0;
        var gDisplacedNoId = 0;

        var gSlp = 0;
        var gSlpDuplicates = 0;
        var gSlpNoId = 0;

        var gEduc = 0;
        var gEducDuplicates = 0;
        var gEducNoId = 0;

        var gFood = 0;
        var gFoodDuplicates = 0;
        var gFoodNoId = 0;

        var gRecords = 0;
        var gDuplicates = 0;
        var gNoId = 0;


        return (
            <div>
                <table className="table table-bordered">
                    <thead>
                        <tr>
                            <th className="text-center" rowSpan="2">MUNICIPALITY</th>
                            <th className="text-center" colSpan="3">TUPAD</th>
                            <th className="text-center" colSpan="3">SLP</th>
                            <th className="text-center" colSpan="3">EDUC</th>
                            <th className="text-center" colSpan="3">FOOD</th>
                            <th className="text-center" colSpan="3">TOTALS</th>
                        </tr>
                        <tr>
                            <th className="text-center">Records</th>
                            <th className="text-center">Duplicates</th>
                            <th className="text-center">No Id</th>

                            <th className="text-center">Records</th>
                            <th className="text-center">Duplicates</th>
                            <th className="text-center">No Id</th>

                            <th className="text-center">Records</th>
                            <th className="text-center">Duplicates</th>
                            <th className="text-center">No Id</th>

                            <th className="text-center">Records</th>
                            <th className="text-center">Duplicates</th>
                            <th className="text-center">No Id</th>

                            <th className="text-center">Records</th>
                            <th className="text-center">Duplicates</th>
                            <th className="text-center">No Id</th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.state.records.map(function (data) {
                            var displacedDuplicates = data.total_displaced - data.total_displaced_uniq;
                            var displacedNoId = data.total_displaced_uniq - data.total_displaced_w_id;
                            var slpDuplicates = data.total_slp - data.total_slp_uniq;
                            var slpNoId = data.total_slp_uniq - data.total_slp_w_id;
                            var educDuplicates = data.total_aics_educ - data.total_aics_educ_uniq;
                            var educNoId = data.total_aics_educ_uniq - data.total_aics_educ_w_id;
                            var foodDuplicates = data.total_aics_food - data.total_aics_food_uniq;
                            var foodNoId = data.total_aics_food_uniq - data.total_aics_food_w_id;

                            var totalRecords = parseInt(data.total_displaced) + parseInt(data.total_slp) + parseInt(data.total_aics_educ) + parseInt(data.total_aics_food);
                            var totalDuplicates = displacedDuplicates + slpDuplicates + educDuplicates + foodDuplicates;
                            var totalNoId = displacedNoId + slpNoId + educNoId + foodNoId;

                            gDisplaced += parseInt(data.total_displaced);
                            gDisplacedDuplicates += displacedDuplicates;
                            gDisplacedNoId += displacedNoId;

                            gSlp += parseInt(data.total_slp);
                            gSlpDuplicates += slpDuplicates;
                            gSlpNoId += slpNoId;

                            gEduc += parseInt(data.total_aics_educ);
                            gEducDuplicates += educDuplicates;
                            gEducNoId += educNoId;

                            gFood += parseInt(data.total_aics_food);
                            gFoodDuplicates += foodDuplicates;
                            gFoodNoId += foodNoId;


                            gRecords += totalRecords;
                            gDuplicates += totalDuplicates;
                            gNoId += totalNoId;

                            return (
                                <tr>
                                    <td className="text-center">{data.source_municipality}</td>
                                    <td className="text-center">{self.numberWithCommas(data.total_displaced)}</td>
                                    <td className="text-center">{self.numberWithCommas(displacedDuplicates)}</td>
                                    <td className="text-center">{self.numberWithCommas(displacedNoId)}</td>
                                    <td className="text-center">{self.numberWithCommas(data.total_slp)}</td>
                                    <td className="text-center">{self.numberWithCommas(slpDuplicates)}</td>
                                    <td className="text-center">{self.numberWithCommas(slpNoId)}</td>
                                    <td className="text-center">{self.numberWithCommas(data.total_aics_educ)}</td>
                                    <td className="text-center">{self.numberWithCommas(educDuplicates)}</td>
                                    <td className="text-center">{self.numberWithCommas(educNoId)}</td>
                                    <td className="text-center">{self.numberWithCommas(data.total_aics_food)}</td>
                                    <td className="text-center">{self.numberWithCommas(foodDuplicates)}</td>
                                    <td className="text-center">{self.numberWithCommas(foodNoId)}</td>
                                    <td className="text-center">{self.numberWithCommas(totalRecords)}</td>
                                    <td className="text-center">{self.numberWithCommas(totalDuplicates)}</td>
                                    <td className="text-center">{self.numberWithCommas(totalNoId)}</td>
                                </tr>
                            )
                        })}
                        <tr>
                            <td className="text-center">TOTALS</td>
                            <td className="text-center"><strong>{self.numberWithCommas(gDisplaced)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gDisplacedDuplicates)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gDisplacedNoId)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gSlp)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gSlpDuplicates)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gSlpNoId)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gEduc)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gEducDuplicates)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gEducNoId)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gFood)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gFoodDuplicates)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gFoodNoId)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gRecords)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gDuplicates)}</strong></td>
                            <td className="text-center"><strong>{self.numberWithCommas(gNoId)}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        )
    }
});

window.TupadProvinceSummary = TupadProvinceSummary;
