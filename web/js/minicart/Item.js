var React = require('react');

module.exports = React.createClass({
  removeItem: function() {
    alert("delete me");
  },
  render: function() {
    var style = {
      'max-width': '100px'
    };
    return (
      <li className="list-group-item">
        <small>{this.props.name} - {this.props.sku}</small>
        <small>{this.props.price}</small>
        <p><img style={style} src="http://magento2.local/pub/media/catalog/product//w/b/wb06-red-0_alt1.jpg" /></p>
        <div className="form-group">
          <label>Qty</label>
          <input value={this.props.qty} type="text" className="form-control mb-2 mr-sm-2 mb-sm-0"/>
          <button type="button" onClick={this.removeItem.bind(this)}>
            <span className="fa fa-trash fa-3" aria-hidden="true"></span>
          </button>
        </div>
      </li>
    );
  }
});
