
<div class="container mt-1">
    <h2>Welcome to Propeller Data transfert for BBV</h2>
    <p>
        The Propeller API requires a token to verify your rights. It is limited to your user. It is not specific to this organisation or site. Do not share this token with anyone. Find my token 
        <a href="https://balfourbeattyvincijv-hs2.prpellr.com/p/settings/wmts-and-api/api" target="_blank" rel="noopener noreferrer">here. <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
    </p>
    <form method="POST" action="/homepage/checkToken" class="d-flex flex-column mt-3 mb-4 p-2 bg-light shadow-sm">
        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1">Propeller API Token</span>
            <input type="password" class="form-control" id="tokenInput" name="tokenInput" pattern=".{47,47}" required>
        </div>
        <div class="col-md-2 mb-3 align-self-center">
            <button type="submit" class="btn btn-primary" id="tokenBtn">Enter</button>
        </div>
    </form>
</div>
