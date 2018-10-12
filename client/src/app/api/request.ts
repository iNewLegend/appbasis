import { API_Client_Http } from "./clients/http";

/**
 * @file: app/api/request
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description:
 * @todo: this is file should be removed.
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class API_Request  {
    protected _name = 'welcome';
    protected _client;
    //----------------------------------------------------------------------

    constructor(client) {
        this._client = client;
    }
    //----------------------------------------------------------------------

    protected get(method: String, callback = null) : any {
        return this._client.get(this._name + '/' + method, callback);
    }
    //----------------------------------------------------------------------
    
    protected post(method: String, params: String, callback = null) {
        return this._client.post(this._name + '/' + method, params, callback);
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------