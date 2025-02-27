<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        nav {
            background-color: #fff;
            color: #333;
            padding: 20px;
            text-align: center;
            display: block;
            margin: 0;
            width: 100%;
        }
        ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: space-evenly;
            height: 120px;
            font-size: 0;
            border-top: 1px solid #ddd;
        }
        li {
            flex: 1;
            text-align: center;
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
            position: relative;
            transition: flex 0.3s ease;
        }
        li:first-child {
            border-left: none;
        }
        li:last-child {
            border-right: none;
        }
        li:hover {
            flex: 2;
            background-color: rgba(230, 230, 230, 1);
            box-shadow: inset 10px 10px 10px -10px rgba(0, 0, 0, 0.3), inset -10px 10px 10px -10px rgba(0, 0, 0, 0.3);
        }
        a {
            color: #1e90ff;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 10px;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }
        a:hover {
            transform: scale(1.1);
        }
        .icon {
            font-size: 2rem;
            margin-bottom: 8px;
        }
        div {
            margin-top: 5px;
            font-weight: 600;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li>
                <a href="accueil.php">
                    <i class="fas fa-globe icon"></i>
                    <div>Accueil</div>
                </a>
            </li>
            <li>
                <a href="dhcp.php">
                    <i class="fas fa-laptop icon"></i>
                    <div>Service DHCP</div>
                </a>
            </li>
            <li>
                <a href="mesuredeb.php">
                    <i class="fas fa-wifi icon"></i>
                    <div>Mon Debit</div>
                </a>
            </li>
            <li>
                <a href="dns.php">
                    <i class="fas fa-server icon"></i>
                    <div>Service DNS</div>
                </a>
            </li>
        </ul>
    </nav>
    
    
    <div id="notif-banner" class="notif-banner" style="display:none;"></div>
</body>
</html>
