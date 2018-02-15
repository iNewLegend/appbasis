/**
 * @file: app/api/request
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description:
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class API_Request  {
    protected _name = 'welcome';
    protected _client;
    //----------------------------------------------------------------------

    constructor(client) {
        this._name = name;
        this._client = client;
    }
    //----------------------------------------------------------------------

    protected get(method: String, callback = null) : any {
        return this._client.get(this._name + '/' + method, callback);
    }
    //----------------------------------------------------------------------
    
    protected post(method: String, params: String, callback = null) {
        this._client.post(this._name + '/' + method, params, callback);
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------