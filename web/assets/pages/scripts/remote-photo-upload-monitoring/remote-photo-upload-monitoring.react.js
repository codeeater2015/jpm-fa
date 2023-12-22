var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var RemotePhotoUploadMonitoring = React.createClass({

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

    componentDidMount: function () {
        this.loadRecentUploads();
        this.loadUser(window.userId);
        this.initSelect2();
    },

    getInitialState: function () {
        return {
            voterGroup: null,
            municipalityName: null,
            brgyNo: null,
            showDatatable: false,
            summaryData: [],
            recentUploads: []
        };
    },

    loadUser: function (userId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_user", { id: userId }),
            type: "GET"
        }).done(function (res) {
            self.setState({ user: res }, self.initSelect2);
        });
    },

    loadUploadSummary: function () {
        var self = this;

        console.log("test");

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_remote_upload_summary_by_municipality", { voterGroup: self.state.voterGroup }),
            type: "GET"
        }).done(function (res) {
            console.log("summary data has been received");
            console.log(res);

            self.setState({ summaryData: res });
        });
    },


    loadRecentUploads: function () {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_remote_upload_recent_upload"),
            type: "GET"
        }).done(function (res) {
            console.log("recent uploads has been received");
            console.log(res);

            self.setState({ recentUploads: res });
        });
    },

    initSelect2: function () {
        var self = this;

        $("#form-voter-group-select2").select2({
            casesentitive: false,
            placeholder: "Enter Category",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_voter_group'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.voter_group, text: item.voter_group };
                        })
                    };
                },
            }
        });

        $("#form-voter-group-select2").on("change", function () {
            self.setState({ "voterGroup": $(this).val() }, self.loadUploadSummary);
        });

        $("#form-voter-group-select2").empty()
            .append($("<option/>")
                .val('LPPP1')
                .text('LPPP1'))
            .trigger("change");
    },

    reloadDatatable: function () {
        console.log("reloading datatable");
        if (this.state.showDatatable)
            this.refs.fieldDatatable.reload();
    },

    render: function () {
        return (
            <div>
                <div className="row">
                    <div className="col-md-8">
                        <div className="portlet light portlet-fit bordered">
                            <div className="portlet-body">
                                <div className="row">
                                    <div className="col-md-8">
                                        <h4><strong>Upload Monitoring</strong></h4>
                                    </div>
                                    <div className="col-md-4">
                                        <FormGroup controlId="formVoterGroup" >
                                            <ControlLabel > Position : </ControlLabel>
                                            <select id="form-voter-group-select2" className="form-control input-sm">
                                            </select>
                                        </FormGroup>
                                    </div>
                                </div>
                                <br />
                                <div className="table-container">
                                    <div className="table-actions-wrapper">
                                    </div>
                                    <table id="remote_photo_upload_table" className="table table-striped table-bordered" width="100%">
                                        <thead className="bg-blue">
                                            <tr>
                                                <th className="text-center">No</th>
                                                <th className="text-center">Municipality</th>
                                                <th className="text-center">Total Uploads</th>
                                                <th className="text-center">Total Downloads</th>
                                                <th className="text-center">Pending Downloads</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {
                                                this.state.summaryData.map(function (item, index) {
                                                    return (
                                                        <tr>
                                                            <td className="text-center">{index + 1}</td>
                                                            <td className="text-center">{item.municipality_name}</td>
                                                            <td className="text-center">{item.total_photos}</td>
                                                            <td className="text-center">{item.total_downloads}</td>
                                                            <td className="text-center">{item.total_pending}</td>
                                                        </tr>
                                                    )
                                                })
                                            }
                                            {
                                                this.state.summaryData.length <= 0 ?
                                                    (
                                                        <tr>
                                                            <th className="text-center" colSpan="5">No upload records was found...</th>
                                                        </tr>
                                                    )
                                                    :
                                                    null
                                            }
                                        </tbody>
                                    </table>
                                </div>


                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="portlet light portlet-fit bordered">
                            <div className="portlet-body">
                                <h4><strong>Recent Activities</strong></h4>
                                <br />

                                <ul>
                                    {
                                        this.state.recentUploads.map(function (item) {
                                            return (
                                                <li>
                                                    New <strong className="font-red">  {item.uploaded_photos} </strong> <strong>{item.voter_group} </strong>uploads from <strong className="font-blue">{item.barangay_name}, {item.municipality_name}</strong>. <br/>  <small> By {item.created_by}  : {item.created_at}</small>
                                                </li>)
                                        })

                                    }
                                    {
                                        this.state.recentUploads.length <= 0 ?
                                            (
                                                <li className="text-center" colSpan="1">No recent uploads...</li>
                                            )
                                            :
                                            null
                                    }
                                </ul>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <RemotePhotoUploadMonitoring />,
        document.getElementById('page-container')
    );
}, 500);
