var SummaryV1Table = React.createClass({

    getInitialState: function () {
        return {
            data: []
        }
    },

    componentDidMount: function () {
        this.loadSummary();
    },

    loadSummary: function () {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_m_get_summary_v1", { 
                clusterName: self.props.clusterName , 
                summaryDate : self.props.summaryDate,
                municipalityNo : self.props.municipalityNo
             }),
            type: "GET"
        }).done(function (res) {
            self.setState({ data: res });
        });
    },

    numberWithCommas : function(x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    render: function () {
        let data = this.state.data;
        let self = this;

        let gPrecincts = 0;
        let gVoters = 0;

        let gTargetTl = 0;
        let gActualTL = 0;

        let gTargetK0 = 0;
        let gActualK0 = 0;
        let gNoHHK0 = 0;

        let gTargetK1 = 0;
        let gActualK1 = 0;
        let gNoHHK1 = 0;

        let gTargetK2 = 0;
        let gActualK2 = 0;
        let gNoHHK2 = 0;

        let gHHMembers = 0;
        let gVerified = 0;

        return (
            <div className="row">
                <div className="col-md-12">
                    <div className="table-container" >
                        <table id="summary_table" className="table table-condensed table-bordered" width="100%">
                            <thead className="bg-green-meadow">
                                <tr >
                                    <th rowSpan="2" >No</th>
                                    <th rowSpan="2" >Barangay</th>
                                    <th rowSpan="2" >Prec</th>
                                    <th rowSpan="2" className="text-center">Reg Voters</th>
                                    <th colSpan="2" className="text-center">TL</th>
                                    <th colSpan="3" className="text-center">K0</th>
                                    <th colSpan="3" className="text-center">K1</th>
                                    <th colSpan="3" className="text-center">K2</th>
                                    <th rowSpan="2" className="text-center">HH Members</th>
                                    <th rowSpan="2" className="text-center">Total Verified</th>
                                    <th rowSpan="2" className="text-center"><small>K4orce & KFC</small></th>
                                    <th rowSpan="2" className="text-center"><small>K4orce Only</small></th>
                                    <th rowSpan="2" className="text-center"><small>TUPAD</small></th>
                                    <th rowSpan="2" className="text-center"><small>TUPAD Non-KFC</small></th>
                                    <th rowSpan="2" className="text-center"><small>Aics/Akap</small></th>
                                    <th rowSpan="2" className="text-center"><small>Aics/Akap Non-KFC</small></th>
                                </tr>
                                <tr >
                                    <th className="text-center">Target</th>
                                    <th className="text-center">Actual</th>

                                    <th className="text-center">Target</th>
                                    <th className="text-center">Actual</th>
                                    <th className="text-center">No HH</th>

                                    <th className="text-center">Target</th>
                                    <th className="text-center">Actual</th>
                                    <th className="text-center">No HH</th>

                                    <th className="text-center">Target</th>
                                    <th className="text-center">Actual</th>
                                    <th className="text-center">No HH</th>

                                </tr>
                            </thead>
                            <tbody className="bg-white">
                                {data.map((item, index) => {
                                    gPrecincts += Number.parseInt(item.total_precincts);
                                    gVoters += Number.parseInt(item.total_registered_voter);
                                    gTargetTl += Number.parseInt(item.target_tl);
                                    gActualTL += Number.parseInt(item.actual_tl);

                                    gTargetK0 += Number.parseInt(item.target_k0);
                                    gActualK0 += Number.parseInt(item.actual_k0);
                                    gNoHHK0 += Number.parseInt(item.no_profile_k0);

                                    
                                    gTargetK1 += Number.parseInt(item.target_k1);
                                    gActualK1 += Number.parseInt(item.actual_k1);
                                    gNoHHK1 += Number.parseInt(item.no_profile_k1);
                                    
                                    gTargetK2 += Number.parseInt(item.target_k2);
                                    gActualK2 += Number.parseInt(item.actual_k2);
                                    gNoHHK2 += Number.parseInt(item.no_profile_k2);

                                    gHHMembers += Number.parseInt(item.hh_members);
                                    gVerified += Number.parseInt(item.total_verified);

                                    return (
                                        <tr>
                                            <td className="text-center">{++index}</td>
                                            <td className="text-center">{item.barangay_name}</td>
                                            <td className="text-center">{self.numberWithCommas(item.total_precincts)}</td>
                                            <td className="text-center">{self.numberWithCommas(item.total_registered_voter)}</td>

                                            <td className="text-center">{item.target_tl == 0 ? "" : self.numberWithCommas(item.target_tl)}</td>
                                            <td className="text-center">{item.actual_tl == 0 ? "" : self.numberWithCommas(item.actual_tl)}</td>

                                            <td className="text-center">{item.target_k0 == 0 ? "" : self.numberWithCommas(item.target_k0)}</td>
                                            <td className="text-center">{item.actual_k0 == 0 ? "" : self.numberWithCommas(item.actual_k0)}</td>
                                            <td className="text-center">{item.no_profile_k0 == 0 ? "" : self.numberWithCommas(item.no_profile_k0)}</td>

                                            <td className="text-center">{item.target_k1 == 0 ? "" : self.numberWithCommas(item.target_k1)}</td>
                                            <td className="text-center">{item.actual_k1 == 0 ? "" : self.numberWithCommas(item.actual_k1)}</td>
                                            <td className="text-center">{item.no_profile_k1 == 0 ? "" : self.numberWithCommas(item.no_profile_k1)}</td>

                                            <td className="text-center">{item.target_k2 == 0 ? "" : self.numberWithCommas(item.target_k2)}</td>
                                            <td className="text-center">{item.actual_k2 == 0 ? "" : self.numberWithCommas(item.actual_k2)}</td>
                                            <td className="text-center">{item.no_profile_k2 == 0 ? "" : self.numberWithCommas(item.no_profile_k2)}</td>

                                            <td className="text-center">{item.hh_members == 0 ? "" : self.numberWithCommas(item.hh_members)}</td>
                                            <td className="text-center">{item.total_verified == 0 ? "" : self.numberWithCommas(item.total_verified)}</td>

                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    );
                                })}

                                <tr>
                                    <td colSpan="2">Total</td>
                                    <td className="text-center"><strong>{self.numberWithCommas(gPrecincts)}</strong></td>
                                    <td  className="text-center"><strong>{self.numberWithCommas(gVoters)}</strong></td>
                                    <td className="text-center"><strong>{self.numberWithCommas(gTargetTl)}</strong></td>
                                    <td className="text-center"><strong>{gActualTL == 0 ? "" : self.numberWithCommas(gActualTL)}</strong></td>

                                    <td className="text-center"><strong>{gTargetK0 == 0 ? "" : self.numberWithCommas(gTargetK0)}</strong></td>
                                    <td className="text-center"><strong>{gActualK0 == 0 ? "" : self.numberWithCommas(gActualK0)}</strong></td>
                                    <td className="text-center"><strong>{gNoHHK0 == 0 ? "" : self.numberWithCommas(gNoHHK0)}</strong></td>

                                    <td className="text-center"><strong>{gTargetK1 == 0 ? "" : self.numberWithCommas(gTargetK1)}</strong></td>
                                    <td className="text-center"><strong>{gActualK1 == 0 ? "" : self.numberWithCommas(gActualK1)}</strong></td>
                                    <td className="text-center"><strong>{gNoHHK1 == 0 ? "" : self.numberWithCommas(gNoHHK1)}</strong></td>

                                    <td className="text-center"><strong>{gTargetK2 == 0 ? "" : self.numberWithCommas(gTargetK2)}</strong></td>
                                    <td className="text-center"><strong>{gActualK2 == 0 ? "" : self.numberWithCommas(gActualK2)}</strong></td>
                                    <td className="text-center"><strong>{gNoHHK2 == 0 ? "" : self.numberWithCommas(gNoHHK2)}</strong></td>

                                    <td className="text-center"><strong>{gHHMembers == 0 ? "" : self.numberWithCommas(gHHMembers)}</strong></td>
                                    <td className="text-center"><strong>{gVerified  == 0 ? "" : self.numberWithCommas(gVerified)}</strong></td>

                                    <td className="text-center"></td>
                                    <td className="text-center"></td>
                                    <td className="text-center"></td>
                                    <td className="text-center"></td>
                                    <td className="text-center"></td>
                                    <td className="text-center"></td>



                                </tr>
                            </tbody>


                        </table>

                    </div>
                </div>
            </div>
        )
    }
});

window.SummaryV1Table = SummaryV1Table;