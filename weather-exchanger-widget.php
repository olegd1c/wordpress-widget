<?php
/**
 * Plugin Name: A simple Widget
 * Description: A widget that displays authors name.
 * Version: 0.1
 * Author: Bilal Shaheen
 * Author URI: http://gearaffiti.com/about
 */


add_action( 'widgets_init', 'weather_exchanger_widget' );

function weather_exchanger_widget() {
	register_widget( 'Weather_Exchanger_Widget' );
}


//-------
class Coord
{
    public $lat; //double
    public $lon; //double
}

class Main
{
    public $temp; //double
    public $pressure; //int
    public $humidity; //int
    public $temp_min; //int
    public $temp_max; //int
}

class Wind
{
    public $speed; //int
    public $deg; //int
}

class Sys
{
    public $country; //String
}

class Clouds
{
    public $all; //int
}

class Weather
{
    public $id; //int
    public $main; //String
    public $description; //String
    public $icon; //String
		
}

class ListWeather
{
    public $id; //int
    public $name; //String
    public $coord; //Coord
    public $main; //Main
    public $dt; //int
    public $wind; //Wind
    public $sys; //Sys
    public $rain; //object
    public $snow; //object
    public $clouds; //Clouds
    public $weather; //array(Weather)
}

class WeatherСontainer
{

	//{"message":"accurate","cod":"200","count":1,"list":[{"id":703448,"name":"Kiev","coord":{"lat":50.4333,"lon":30.5167},"main":{"temp":-0.66,"pressure":1031,"humidity":80,"temp_min":-1,"temp_max":0},"dt":1483003800,"wind":{"speed":7,"deg":360},"sys":{"country":"UA"},"rain":null,"snow":null,"clouds":{"all":90},"weather":[{"id":600,"main":"Snow","description":"light snow","icon":"13d"}]}]}
	public $message; //String
    public $cod; //String
    public $count; //int
    public $list; //array(ListWeather)
	
	public function set($data){		
		foreach ($data as $key => $value) {
			$this->{$key} = $value;
		}
	}	
	
	public function getList(){
		return $this->list;
	}
}

//---------

class СurrencyСontainer
{
    //{"r030":840,"txt":"Долар США","rate":26.893158,"cc":"USD","exchangedate":"29.12.2016"}
	public $r030; //int
    public $txt; //String
    public $rate; //double
    public $cc; //String
    public $exchangedate; //String
	
	public function set($data){		
		foreach ($data as $key => $value) {
			$this->{$key} = $value;
		}
	}
}

class Weather_Exchanger_Widget extends WP_Widget {
	private $name_widget = 'weather_exchanger_widget_domain';
	private $description_widget = 'Погода и Курс валют';
	private $author_widget = 'Oleg D';
	private $id_widget = 'weather-exchanger-widget';
	private $url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=USD&json';
	//replace YOU-KEY
	private $url_weather = 'http://api.openweathermap.org/data/2.5/find?q=Kiev&appid=YOU-KEY&units=metric&lang=ru';
	function Weather_Exchanger_Widget() {
		$widget_ops = array( 'classname' => $this->name_widget, 'description' => __('Виджет показывает погоду и курс валют', $this->name_widget) );

		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => $this->id_widget );

		$this->WP_Widget( $this->id_widget, __($this->description_widget, $this->name_widget), $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		$name = $instance['name'];
		$show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;

		echo $before_widget;

		// Display the widget title
		if ( $title )
			echo $before_title . $title . $after_title;

		// Display body widget
		$this->addWeather();								
		printf( '<br>' );
		printf( '<br>' );
		$this->addCourse();		
		// - Display body widget
		
		echo $after_widget;
	}

	//Update the widget

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['name'] = strip_tags( $new_instance['name'] );

		return $instance;
	}

	function addWeather(){
		printf( '<i>Погода</i><br>' );
		$json = file_get_contents($this->url_weather);
		$data = json_decode($json); //true 
		//var_dump($data);
		$weather = new WeatherСontainer();
		$weather->set($data);				
		
		$array = array('Kiev' => 'Киев');		
		
		foreach ($weather->getList() as $key => $row) {
			//var_dump($row);
			//printf( $row->name.' : '.round($row['main']['temp']));				
			printf( $array[$row->name].' : '.round($row->main->temp));				
		}		
	}	

	function addCourse(){
		printf( '<i>Курс валют НБУ</i><br>' );
		
		$json = file_get_contents($this->url);
		$data = json_decode($json,true);

		$currency = new СurrencyСontainer();
		$currency->set($data[0]);		
		
		printf( ''.$currency->cc.' : '.$currency->rate);		
	}	
	
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => __($this->description_widget, $this->name_widget), 'name' => __($this->author_widget, $this->name_widget));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>


		//Widget Title: Text Input.
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', $this->name_widget); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>



	<?php
	}
}

?>