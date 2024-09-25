var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var KamadaPrelistingCreateModal = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                    eventName : "",
                    eventDate : null
                },
                errors : []
            }
        };
    },

    render : function(){
        var self = this;

        return (
            <Modal style={{ marginTop : "10px" }} keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Create New KForce List</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="event-form" >
                        <FormGroup controlId="formPrelistingDesc" validationState={this.getValidationState('prelistingDesc')}>
                            <ControlLabel> Description : </ControlLabel>
                            <FormControl bsClass="form-control input-sm" name="prelistingDesc" value={this.state.form.data.prelistingDesc} onChange={this.setFormProp}/>
                            <HelpBlock>{this.getError('prelistingDesc')}</HelpBlock>
                        </FormGroup>

                        <div className="col-md-4" style={{ paddingRight : "0" , paddingLeft : "0" }}>
                            <FormGroup controlId="formPrelistingDate" validationState={this.getValidationState('prelistingDate')}>
                                <ControlLabel> Date : </ControlLabel>
                                <FormControl type="date" bsClass="form-control input-sm" name="prelistingDate" value={this.state.form.data.prelistingDate} onChange={this.setFormProp}/>
                                <HelpBlock>{this.getError('prelistingDate')}</HelpBlock>
                            </FormGroup>
                        </div>
                        
                        <div className="clearfix"/>

                        <FormGroup controlId="formRemarks" validationState={this.getValidationState('remarks')}>
                            <ControlLabel> Remarks : </ControlLabel>
                            <FormControl componentClass="textarea" rows="6" bsClass="form-control input-sm" name="remarks" value={this.state.form.data.remarks} onChange={this.setFormProp}/>
                            <HelpBlock>{this.getError('remarks')}</HelpBlock>
                        </FormGroup>

                        <div className="text-right" >
                            <button type="button" className="btn blue-madison" onClick={this.submit}>Submit</button>
                            <button type="button" className="btn  btn-default" style={{marginLeft : "10px"}}  onClick={this.props.onHide}>Close</button>
                        </div>
                        
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount : function(){
    
    },

    setFormPropValue : function(field,value){
        var form = this.state.form;
        form.data[field] = value;
        this.setState({form : form});
    },

    setFormProp : function(e){
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({form : form});
    },

    setErrors : function(errors){
        var form = this.state.form;
        form.errors = errors;
        this.setState({form : form});
    },

    getError : function(field){
        var errors = this.state.form.errors;
        for(var errorField in errors){
            if(errorField == field)
                return errors[field];
        }
        return null;
    },

    getValidationState : function(field){
        return this.getError(field) != null ? 'error' : '';
    },

    isEmpty : function(value){
        return value == null || value == '';
    },

    reset : function(){
      var form = this.state.form;
      form.errors = [];

      this.setState({form : form});
    },

    submit : function(e){
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.proId = self.props.proId;

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_kamada_prelisting"),
            data: data,
            type: 'POST'
        }).done(function(res){
            self.reset();
            self.props.reload();
            self.props.onHide();
        }).fail(function(err){
             self.setErrors(err.responseJSON);
        });
    }
});


window.KamadaPrelistingCreateModal = KamadaPrelistingCreateModal;