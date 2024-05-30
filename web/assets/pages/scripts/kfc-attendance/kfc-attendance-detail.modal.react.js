var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;
var Tab = ReactBootstrap.Tab;
var Tabs = ReactBootstrap.Tabs;
var Nav = ReactBootstrap.Nav;
var NavItem = ReactBootstrap.NavItem;

var KfcAttendanceDetailModal = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showAssignmentModal: false,
            showProfileModal : false,
            active: "profile"
        };
    },

    setSelectedTab: function (key) {
        this.setState({ active: key });
    },

    closeAssignmentModal : function(){
        this.setState({ showAssignmentModal : false });
    },

    onProfilesCreateSuccess : function(){
        this.refs.profileDatatable.reload();
        this.props.reloadDetail();
    },

    onAssignmentCreateSuccess : function(){
        this.refs.assignmentDatatable.reload();
        this.props.reloadDetail();
    },

    openAssignmentModal : function(){
        this.setState({ showAssignmentModal : true });
    },

    openProfileModal : function(){
        this.setState({ showProfileModal : true });
    },

    closeProfileModal : function(){
        this.setState({ showProfileModal : false});
    },

    render: function () {
        var self = this;
        return (
            <Modal style={{ marginTop: "50px" }} dialogClassName="modal-custom-70" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Attendance Detail</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <Tab.Container activeKey={this.state.active} onSelect={this.setSelectedTab}>
                        <div className="row" >
                            <div className="col-md-12 m-t-sm">
                                <Nav bsStyle="tabs">
                                    <NavItem eventKey="profile">
                                        Profile
                                    </NavItem>
                                    <NavItem eventKey="assignment">
                                        Assignment
                                    </NavItem>
                                </Nav>
                            </div>
                            <div className="col-md-12 m-t-sm">
                                <Tab.Content animation>
                                    <Tab.Pane eventKey="profile">
                                        <div className="portlet-body" id="bcbp_component">
                                            <div className="row">
                                                <div className="col-md-12">
                                                    <br />
                                                    <button type="button" className="btn btn-success btn-sm" onClick={this.openProfileModal} style={{ marginRight: "10px" }} >Add household member</button>
                                                </div>
                                            </div>
                                            <div className="row">

                                                {
                                                    this.state.showProfileModal &&
                                                    <KfcAttendanceProfileCreateModal
                                                        show={this.state.showProfileModal}
                                                        onHide={this.closeProfileModal}
                                                        onSuccess={this.onProfilesCreateSuccess}
                                                        hdrId={this.props.hdrId}
                                                    />
                                                }
                                                <div className="col-md-12">
                                                    <KfcAttendanceProfileDatatable reloadDetail={this.props.reloadDetail} ref="profileDatatable" hdrId={this.props.hdrId} />
                                                </div>
                                            </div>
                                        </div>
                                    </Tab.Pane>
                                    <Tab.Pane eventKey="assignment">
                                        <div className="portlet-body" id="bcbp_component">
                                            <div className="row">
                                                <div className="col-md-12">
                                                    <br />
                                                    <button type="button" className="btn btn-success btn-sm" style={{ marginRight: "10px" }} onClick={this.openAssignmentModal} >Add assignment</button>
                                                </div>
                                            </div>
                                            <div className="row">

                                                {
                                                    this.state.showAssignmentModal &&
                                                    <KfcAttendanceAssignmentCreateModal
                                                        show={this.state.showAssignmentModal}
                                                        onHide={this.closeAssignmentModal}
                                                        onSuccess={this.onAssignmentCreateSuccess}
                                                        hdrId={this.props.hdrId}
                                                    />
                                                }

                                                <div className="col-md-12">
                                                    <KfcAttendanceAssignmentDatatable reloadDetail={this.props.reloadDetail} ref="assignmentDatatable" hdrId={this.props.hdrId} />
                                                </div>
                                            </div>
                                        </div>
                                    </Tab.Pane>
                                </Tab.Content>
                            </div>
                        </div>
                    </Tab.Container>
                </Modal.Body>
            </Modal>
        );
    }
});


window.KfcAttendanceDetailModal = KfcAttendanceDetailModal;