
<script>
    $(document).ready(function () {



    }); //end document on ready

</script>

</head>

<body>


        <div id="content">
            <div class="wrapper">

                <div class="panel left" style="position:fixed;">
                   Welcome
                   <?php  echo $cur_user->username; ?>
                </div>
                <br>
                <form action = "logout">
                    <input type="submit" value="Logout" />
                </form>


                <div class="Job Panel">
                    <table>
                        <tr>
                            <td> Job Name </td>
                            <td> Job Description </td>
                            <td> Job price </td>
                            <td> Job Coord </td>
                        </tr>
                    <?php foreach ($jobs as $job){ ?>
                            <tr> <td> <?php echo $job->job_name; ?> </td>
                                <td> <?php echo $job->job_description; ?> </td>
                                <td> <?php echo $job->job_price; ?> </td>
                                <td> <?php echo ($job->job_latitiude.",".$job->job_longitude); ?> </td> </tr>
                        <!--     echo "<tr>";
                            echo "<td> ($job->job_name) </td>";
                            echo "<td> ($job->job_description) </td>";
                            echo "<td> ($job->job_price) </td>";
                            echo "<td>(".($job->job_lat).,.($job->job_"</td>";
                            echo "</tr>"; -->
                            <?php
                        }
                    ?>
                    </table>
                </div>
            </div>
        </div>
</body>